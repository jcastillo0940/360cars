import './bootstrap';

const body = document.body;
const topbar = document.querySelector('[data-topbar]');
const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const reveals = document.querySelectorAll('.reveal');

const syncMenuState = (expanded) => {
    if (!menuToggle || !mobileMenu) return;

    menuToggle.setAttribute('aria-expanded', String(expanded));
    mobileMenu.hidden = !expanded;
    body.classList.toggle('menu-open', expanded);
};

menuToggle?.addEventListener('click', () => {
    const expanded = menuToggle.getAttribute('aria-expanded') === 'true';
    syncMenuState(!expanded);
});

mobileMenu?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => syncMenuState(false));
});

sidebarToggle?.addEventListener('click', () => {
    const isOpen = body.classList.toggle('sidebar-open');
    sidebarToggle.setAttribute('aria-expanded', String(isOpen));
});

const onScroll = () => {
    topbar?.classList.toggle('is-scrolled', window.scrollY > 18);
};

onScroll();
window.addEventListener('scroll', onScroll, { passive: true });

if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.16 });

    reveals.forEach((element) => observer.observe(element));
} else {
    reveals.forEach((element) => element.classList.add('is-visible'));
}

const accordions = document.querySelectorAll('[data-accordion]');

accordions.forEach((accordion) => {
    const trigger = accordion.querySelector('[data-accordion-trigger]');
    const panel = accordion.querySelector('[data-accordion-panel]');
    const icon = accordion.querySelector('.accordion__icon');

    trigger?.addEventListener('click', () => {
        const expanded = trigger.getAttribute('aria-expanded') === 'true';
        trigger.setAttribute('aria-expanded', String(!expanded));
        panel.hidden = expanded;
        if (icon) icon.textContent = expanded ? '+' : '-';
    });
});

const tabGroups = document.querySelectorAll('[data-tabs]');

tabGroups.forEach((group) => {
    const triggers = group.querySelectorAll('[data-tab-trigger]');
    const panels = group.querySelectorAll('[data-tab-panel]');

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const target = trigger.dataset.tabTrigger;

            triggers.forEach((button) => {
                button.classList.toggle('is-active', button === trigger);
            });

            panels.forEach((panel) => {
                const isActive = panel.dataset.tabPanel === target;
                panel.hidden = !isActive;
                panel.classList.toggle('is-active', isActive);
            });
        });
    });
});

const createDraftPhotoStore = (storageKey) => {
    if (!storageKey || !('indexedDB' in window)) {
        return {
            getAll: async () => ({}),
            set: async () => {},
            remove: async () => {},
            clear: async () => {},
        };
    }

    const dbName = 'movikaa-autosave';
    const storeName = 'draft-files';

    const open = () => new Promise((resolve, reject) => {
        const request = window.indexedDB.open(dbName, 1);

        request.onupgradeneeded = () => {
            const db = request.result;
            if (!db.objectStoreNames.contains(storeName)) {
                db.createObjectStore(storeName, { keyPath: 'key' });
            }
        };

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });

    return {
        getAll: async () => {
            const db = await open();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction(storeName, 'readonly');
                const store = transaction.objectStore(storeName);
                const request = store.getAll();

                request.onsuccess = () => {
                    const entries = {};
                    (request.result || []).forEach((item) => {
                        if (String(item.key).startsWith(`${storageKey}:`)) {
                            entries[String(item.key).replace(`${storageKey}:`, '')] = item.file;
                        }
                    });
                    db.close();
                    resolve(entries);
                };

                request.onerror = () => {
                    db.close();
                    reject(request.error);
                };
            });
        },
        set: async (name, file) => {
            const db = await open();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction(storeName, 'readwrite');
                transaction.objectStore(storeName).put({ key: `${storageKey}:${name}`, file });
                transaction.oncomplete = () => {
                    db.close();
                    resolve();
                };
                transaction.onerror = () => {
                    db.close();
                    reject(transaction.error);
                };
            });
        },
        remove: async (name) => {
            const db = await open();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction(storeName, 'readwrite');
                transaction.objectStore(storeName).delete(`${storageKey}:${name}`);
                transaction.oncomplete = () => {
                    db.close();
                    resolve();
                };
                transaction.onerror = () => {
                    db.close();
                    reject(transaction.error);
                };
            });
        },
        clear: async () => {
            const db = await open();

            return new Promise((resolve, reject) => {
                const transaction = db.transaction(storeName, 'readwrite');
                const store = transaction.objectStore(storeName);
                const request = store.getAllKeys();

                request.onsuccess = () => {
                    (request.result || [])
                        .filter((key) => String(key).startsWith(`${storageKey}:`))
                        .forEach((key) => store.delete(key));
                };

                request.onerror = () => {
                    db.close();
                    reject(request.error);
                };

                transaction.oncomplete = () => {
                    db.close();
                    resolve();
                };
                transaction.onerror = () => {
                    db.close();
                    reject(transaction.error);
                };
            });
        },
    };
};

const createAutosaveManager = (form, statusNode) => {
    const storageKey = form.dataset.autosaveKey;
    const photoStore = createDraftPhotoStore(storageKey);

    if (!storageKey || !('localStorage' in window)) {
        return {
            restore: async () => {},
            save: async () => {},
            clear: async () => {},
            stamp: () => {},
            savePhoto: async () => {},
            removePhoto: async () => {},
        };
    }

    const setStatus = (message, tone = 'idle') => {
        if (!statusNode) return;
        statusNode.textContent = message;
        statusNode.dataset.state = tone;
    };

    const updatePhotoDraftStatus = (restoredCount = null) => {
        const draftNode = form.querySelector('[data-photo-draft-status]');
        if (!draftNode) return;

        const inputs = Array.from(form.querySelectorAll('input[type="file"][data-photo-input]'));
        const attachedCount = inputs.filter((input) => input.files?.length).length;

        if (attachedCount > 0) {
            draftNode.hidden = false;
            draftNode.textContent = restoredCount && restoredCount > 0
                ? `Recuperamos ${restoredCount} foto${restoredCount === 1 ? '' : 's'} del borrador. Ya quedaron cargadas en este paso.`
                : `Tienes ${attachedCount} foto${attachedCount === 1 ? '' : 's'} lista${attachedCount === 1 ? '' : 's'} en este borrador.`;
            return;
        }

        draftNode.hidden = true;
        draftNode.textContent = 'No hay fotos restauradas todavía.';
    };

    const getPayload = () => {
        const payload = {};
        const fields = form.querySelectorAll('input[name], select[name], textarea[name]');

        fields.forEach((field) => {
            if (field.type === 'file' || field.type === 'password') {
                return;
            }

            if (field.type === 'checkbox') {
                if (field.name.endsWith('[]')) {
                    const key = field.name;
                    payload[key] = payload[key] || [];
                    if (field.checked) {
                        payload[key].push(field.value);
                    }
                    return;
                }

                payload[field.name] = field.checked;
                return;
            }

            if (field.type === 'radio') {
                if (field.checked) {
                    payload[field.name] = field.value;
                }
                autosave?.removePhoto(input);
                return;
            }

            payload[field.name] = field.value;
        });

        return payload;
    };

    const restore = async () => {
        try {
            const raw = window.localStorage.getItem(storageKey);
            if (!raw) return;

            const draft = JSON.parse(raw);
            const values = draft.values || {};

            Object.entries(values).forEach(([name, value]) => {
                const fields = form.querySelectorAll(`[name="${window.CSS?.escape ? window.CSS.escape(name) : name}"]`);

                fields.forEach((field) => {
                    if (field.type === 'file' || field.type === 'password') {
                        return;
                    }

                    if (field.type === 'checkbox' && field.name.endsWith('[]')) {
                        field.checked = Array.isArray(value) && value.includes(field.value);
                        return;
                    }

                    if (field.type === 'checkbox') {
                        field.checked = Boolean(value);
                        return;
                    }

                    if (field.type === 'radio') {
                        field.checked = value === field.value;
                        return;
                    }

                    if (!field.value) {
                        field.value = value;
                    }
                });
            });

            const files = await photoStore.getAll();
            const restoredEntries = Object.entries(files);
            restoredEntries.forEach(([name, file]) => {
                const input = form.querySelector(`input[type="file"][name="${window.CSS?.escape ? window.CSS.escape(name) : name}"]`);
                if (!input || !file) return;

                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });

            updatePhotoDraftStatus(restoredEntries.length);

            if (draft.savedAt) {
                setStatus(`Borrador restaurado. Ultimo guardado ${draft.savedAt}.`, 'restored');
            }
        } catch {
            setStatus('No se pudo restaurar el borrador local.', 'error');
        }
    };

    let timer = null;

    const save = async () => {
        try {
            const savedAt = new Date().toLocaleTimeString('es-CR', {
                hour: '2-digit',
                minute: '2-digit',
            });

            window.localStorage.setItem(storageKey, JSON.stringify({
                savedAt,
                values: getPayload(),
            }));

            setStatus(`Borrador guardado automaticamente a las ${savedAt}.`, 'saved');
        } catch {
            setStatus('No se pudo guardar el borrador automatico.', 'error');
        }
    };

    const stamp = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => {
            save();
        }, 250);
    };

    const clear = async () => {
        window.localStorage.removeItem(storageKey);
        await photoStore.clear();
        updatePhotoDraftStatus(0);
        setStatus('Borrador local limpiado porque el envio termino.', 'done');
    };

    const savePhoto = async (input) => {
        if (!input?.name) return;
        const file = input.files?.[0];

        if (!file) {
            await photoStore.remove(input.name);
            updatePhotoDraftStatus();
            return;
        }

        await photoStore.set(input.name, file);
        updatePhotoDraftStatus();
    };

    const removePhoto = async (input) => {
        if (!input?.name) return;
        await photoStore.remove(input.name);
        updatePhotoDraftStatus();
    };

    form.addEventListener('input', stamp);
    form.addEventListener('change', stamp);

    return { restore, save, clear, stamp, savePhoto, removePhoto };
};
const makeFileFromBlob = (blob, originalFile) => {
    const extension = originalFile.name.includes('.') ? originalFile.name.split('.').pop() : 'jpg';
    const baseName = originalFile.name.replace(/\.[^.]+$/, '');
    const type = blob.type || originalFile.type || 'image/jpeg';

    return new File([blob], `${baseName}-optimized.${extension}`, {
        type,
        lastModified: Date.now(),
    });
};

const compressImageFile = (file, { maxWidth = 1920, quality = 0.84 } = {}) => new Promise((resolve) => {
    if (!file.type.startsWith('image/')) {
        resolve(file);
        return;
    }

    const reader = new FileReader();
    reader.onload = () => {
        const image = new Image();

        image.onload = () => {
            let width = image.width;
            let height = image.height;

            if (width > maxWidth) {
                const ratio = maxWidth / width;
                width = Math.round(width * ratio);
                height = Math.round(height * ratio);
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const context = canvas.getContext('2d');

            if (!context) {
                resolve(file);
                return;
            }

            context.drawImage(image, 0, 0, width, height);
            canvas.toBlob((blob) => {
                if (!blob || blob.size >= file.size) {
                    resolve(file);
                    return;
                }

                resolve(makeFileFromBlob(blob, file));
            }, 'image/jpeg', quality);
        };

        image.onerror = () => resolve(file);
        image.src = String(reader.result);
    };

    reader.onerror = () => resolve(file);
    reader.readAsDataURL(file);
});

const wirePhotoHints = (wizard, autosave = null) => {
    const photoInputs = wizard.querySelectorAll('[data-photo-input]');
    const sidebarLoader = document.querySelector('[data-sidebar-loader]');

    photoInputs.forEach((input) => {
        const card = input.closest('.seller-photo-card');
        const hint = card?.querySelector('[data-file-hint]');

        if (card && !card.querySelector('[data-photo-preview]')) {
            const preview = document.createElement('div');
            preview.className = 'seller-photo-preview';
            preview.dataset.photoPreview = 'true';
            preview.innerHTML = '<span class="material-symbols-outlined">image</span><strong>Vista previa</strong><small>Aún no has seleccionado una foto.</small>';
            card.insertBefore(preview, input);
        }

        input.addEventListener('change', async () => {
            const file = input.files?.[0];
            const preview = card?.querySelector('[data-photo-preview]');

            if (!file || !card) {
                card?.classList.remove('is-ready');
                if (preview) {
                    preview.classList.remove('has-image');
                    preview.style.backgroundImage = '';
                    preview.innerHTML = '<span class="material-symbols-outlined">image</span><strong>Vista previa</strong><small>Aún no has seleccionado una foto.</small>';
                }
                return;
            }

            card.classList.add('is-processing');
            if (hint) {
                hint.textContent = `Preparando ${file.name}...`;
            }
            if (sidebarLoader) {
                sidebarLoader.textContent = `Comprimiendo ${file.name} para que suba más rápido...`;
            }

            const optimizedFile = await compressImageFile(file);
            const transfer = new DataTransfer();
            transfer.items.add(optimizedFile);
            input.files = transfer.files;
            await autosave?.savePhoto(input);

            card.classList.remove('is-processing');
            card.classList.add('is-ready');

            if (hint) {
                const sizeMb = (optimizedFile.size / 1024 / 1024).toFixed(2);
                hint.textContent = `${optimizedFile.name} listo (${sizeMb} MB).`;
            }
            if (preview) {
                const imageUrl = URL.createObjectURL(optimizedFile);
                preview.classList.add('has-image');
                preview.style.backgroundImage = `linear-gradient(180deg, rgba(15,23,42,0.02) 0%, rgba(15,23,42,0.55) 100%), url("${imageUrl}")`;
                preview.innerHTML = `<span class="seller-photo-preview__badge">Lista</span><strong>${optimizedFile.name}</strong><small>Imagen optimizada y lista para enviarse.</small>`;
            }
            if (sidebarLoader) {
                sidebarLoader.textContent = `${optimizedFile.name} qued? listo. Puedes seguir agregando fotos.`;
            }
        });
    });
};

const wireModelFiltering = (wizard) => {
    const makeSelect = wizard.querySelector('[data-make-select]');
    const modelSelect = wizard.querySelector('[data-model-select]');

    if (!makeSelect || !modelSelect) return;

    const options = Array.from(modelSelect.querySelectorAll('option[data-make]'));

    const syncModels = () => {
        const selectedMake = makeSelect.value;
        let selectedStillVisible = false;

        options.forEach((option) => {
            const isVisible = !selectedMake || option.dataset.make === selectedMake;
            option.hidden = !isVisible;
            option.disabled = !isVisible;

            if (isVisible && option.value === modelSelect.value) {
                selectedStillVisible = true;
            }
        });

        if (!selectedStillVisible) {
            modelSelect.value = '';
        }
    };

    makeSelect.addEventListener('change', syncModels);
    syncModels();
};

const wirePricePreview = (wizard) => {
    const input = wizard.querySelector('[data-price-input]');
    const preview = wizard.querySelector('[data-price-preview]');
    const usdToCrc = Number(wizard.dataset.usdToCrc || 0);

    if (!input || !preview) return;

    const formatCRC = (amount) => new Intl.NumberFormat('es-CR', {
        style: 'currency',
        currency: 'CRC',
        maximumFractionDigits: 0,
    }).format(amount);

    const formatUSD = (amount) => new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);

    const syncPrice = () => {
        const amount = Number(input.value || 0);

        if (!amount) {
            preview.textContent = 'El precio oficial se mostrar? en colones. Debajo ver?s una referencia peque?a en d?lares.';
            return;
        }

        const crcLabel = formatCRC(amount);
        const usdLabel = usdToCrc > 0 ? ` Aproximado: ${formatUSD(amount / usdToCrc)}.` : '';
        preview.textContent = `Vista previa: ${crcLabel}.${usdLabel}`.trim();
    };

    input.addEventListener('input', syncPrice);
    syncPrice();
};

const wirePhotoSequence = (wizard) => {
    const sequence = wizard.querySelector('[data-photo-sequence]');
    if (!sequence) return;

    const panels = Array.from(sequence.querySelectorAll('[data-photo-panel]'));
    const optionalEntry = sequence.querySelector('[data-photo-optional-entry]');
    const meta = sequence.querySelector('[data-photo-sequence-meta]');
    const prevButton = sequence.querySelector('[data-photo-prev]');
    const nextButton = sequence.querySelector('[data-photo-next]');
    const optionalOpen = sequence.querySelector('[data-photo-optional-open]');
    const optionalSkip = sequence.querySelector('[data-photo-optional-skip]');
    const sidebarLoader = document.querySelector('[data-sidebar-loader]');
    const stepNextButton = wizard.querySelector('[data-photo-step-next]');
    const stepStatus = wizard.querySelector('[data-photo-step-status]');
    const stepCopy = wizard.querySelector('[data-photo-step-copy]');
    const requiredPanels = panels.filter((panel) => panel.dataset.photoOptional !== 'true');
    const optionalPanels = panels.filter((panel) => panel.dataset.photoOptional === 'true');
    const requiredInputs = requiredPanels.map((panel) => panel.querySelector('input[type="file"]')).filter(Boolean);

    let mode = 'required';
    let currentIndex = 0;

    const updateMeta = (label) => {
        if (meta) meta.textContent = label;
    };

    const getCurrentPanel = () => panels.find((panel) => !panel.hidden);

    const validateCurrentPhoto = () => {
        const panel = getCurrentPanel();
        if (!panel) return true;
        const input = panel.querySelector('input[type="file"]');
        if (!input) return true;
        return input.reportValidity();
    };

    const syncStepGate = () => {
        const readyCount = requiredInputs.filter((input) => input.files?.length).length;
        const remaining = Math.max(requiredInputs.length - readyCount, 0);
        const allReady = remaining === 0;

        if (stepNextButton) {
            stepNextButton.disabled = !allReady;
            stepNextButton.classList.toggle('is-pulse-locked', !allReady);
            stepNextButton.classList.toggle('is-ready-to-continue', allReady);
        }

        if (stepStatus) {
            stepStatus.textContent = allReady ? 'Fotos obligatorias completas' : `Faltan ${remaining} foto${remaining === 1 ? '' : 's'}`;
        }

        if (stepCopy) {
            stepCopy.textContent = allReady
                ? 'Perfecto. Ya puedes continuar a la ubicación o agregar un par de fotos opcionales.'
                : 'Sube frontal, trasera, laterales e interiores antes de continuar a la ubicación.';
        }
    };

    const showOptionalEntry = (message = 'Ya completaste las fotos base. Puedes continuar o subir dos extras.') => {
        panels.forEach((panel) => {
            panel.hidden = true;
        });
        if (optionalEntry) {
            optionalEntry.hidden = false;
            const paragraph = optionalEntry.querySelector('p');
            if (paragraph) paragraph.textContent = message;
        }
        if (prevButton) prevButton.hidden = false;
        if (nextButton) nextButton.hidden = true;
        updateMeta('Fotos base completas');
        if (sidebarLoader) {
            sidebarLoader.textContent = 'Las fotos obligatorias ya quedaron listas. Puedes continuar o subir dos adicionales.';
        }
        syncStepGate();
    };

    const sync = () => {
        const activePanels = mode === 'optional' ? optionalPanels : requiredPanels;
        panels.forEach((panel) => {
            panel.hidden = true;
        });
        if (optionalEntry) optionalEntry.hidden = true;

        const panel = activePanels[currentIndex];
        if (!panel) {
            showOptionalEntry();
            return;
        }

        panel.hidden = false;
        const title = panel.dataset.photoTitle || 'Foto';

        if (prevButton) prevButton.hidden = mode === 'required' && currentIndex === 0;
        if (nextButton) {
            nextButton.hidden = false;
            nextButton.textContent = mode === 'optional' && currentIndex === activePanels.length - 1
                ? 'Terminar fotos'
                : 'Foto siguiente';
        }

        if (mode === 'required') {
            updateMeta(`Foto ${currentIndex + 1} de ${requiredPanels.length}`);
        } else {
            updateMeta(`Opcional ${currentIndex + 1} de ${optionalPanels.length}`);
        }

        if (sidebarLoader) {
            sidebarLoader.textContent = `Sube ${title.toLowerCase()} y luego contin?a con la siguiente fotograf?a.`;
        }

        syncStepGate();
    };

    prevButton?.addEventListener('click', () => {
        if (mode === 'optional' && currentIndex === 0) {
            mode = 'required';
            currentIndex = requiredPanels.length - 1;
            sync();
            return;
        }

        if (mode === 'required' && currentIndex === 0) {
            return;
        }

        currentIndex -= 1;
        sync();
    });

    nextButton?.addEventListener('click', () => {
        if (!validateCurrentPhoto()) return;

        if (mode === 'required') {
            if (currentIndex === requiredPanels.length - 1) {
                showOptionalEntry();
                return;
            }
            currentIndex += 1;
            sync();
            return;
        }

        if (currentIndex === optionalPanels.length - 1) {
            showOptionalEntry('Tus fotos opcionales también quedaron listas. Ya puedes pasar al siguiente paso.');
            return;
        }

        currentIndex += 1;
        sync();
    });

    optionalOpen?.addEventListener('click', () => {
        mode = 'optional';
        currentIndex = 0;
        sync();
    });

    optionalSkip?.addEventListener('click', () => {
        showOptionalEntry('Perfecto. Dejamos solo las fotos base y ya puedes pasar al siguiente paso.');
    });

    requiredInputs.forEach((input) => {
        input.addEventListener('change', () => {
            window.setTimeout(syncStepGate, 30);
        });
    });

    stepNextButton?.addEventListener('click', (event) => {
        if (stepNextButton.disabled) {
            event.preventDefault();
            const currentRequiredPanel = requiredPanels.find((panel) => {
                const input = panel.querySelector('input[type="file"]');
                return input && !input.files?.length;
            });
            currentRequiredPanel?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    sync();
};

const validateStep = (panel) => {
    const fields = Array.from(panel.querySelectorAll('input, select, textarea')).filter((field) => !field.disabled && field.type !== 'hidden');

    for (const field of fields) {
        if (!field.reportValidity()) {
            field.focus();
            return false;
        }
    }

    return true;
};

const wizard = document.querySelector('[data-wizard]');

if (wizard) {
    const panels = Array.from(wizard.querySelectorAll('[data-step-panel]'));
    const indicators = Array.from(wizard.querySelectorAll('[data-step-indicator]'));
    const statusNode = document.querySelector('[data-autosave-status]');
    const progressHeading = document.querySelector('[data-progress-heading]');
    const progressCopy = document.querySelector('[data-progress-copy]');
    const progressPercent = document.querySelector('[data-progress-percent]');
    const progressBar = document.querySelector('[data-progress-bar]');
    const progressRing = document.querySelector('.seller-progress-ring');
    const currentStepTitle = document.querySelector('[data-current-step-title]');
    const currentStepMeta = document.querySelector('[data-current-step-meta]');
    const sidebarTip = document.querySelector('[data-sidebar-tip]');
    const sidebarLoader = document.querySelector('[data-sidebar-loader]');
    const autosave = createAutosaveManager(wizard, statusNode);
    let stepIndex = panels.findIndex((panel) => !panel.hidden);
    if (stepIndex < 0) stepIndex = 0;
    const stepTips = [
        'Usa el nombre comercial correcto de la versi?n y una descripci?n breve pero confiable.',
        'Completa kilometraje, motor, combustible y precio en colones con datos exactos.',
        'Las fotos frontal, trasera, laterales e interiores suelen acelerar los contactos.',
        'Busca el punto exacto del auto dentro de Costa Rica para ubicarlo mejor.',
        'Cierra con correo o teléfono y podrás manejar este y otros autos desde tu panel.',
    ];
    const stepSummaries = [
        'Completa los datos base del auto para arrancar bien tu publicaci?n.',
        'Normaliza precio, mec?nica y condici?n para que el anuncio sea comparable.',
        'Agrega extras y fotos claras para elevar confianza y conversión.',
        'Confirma la ubicación exacta del auto dentro de Costa Rica.',
        'Termina creando tu cuenta para publicar y administrar este y futuros autos.',
    ];

    const syncWizard = (scroll = true) => {
        panels.forEach((panel, index) => {
            const isActive = index === stepIndex;
            panel.hidden = !isActive;
            panel.classList.toggle('is-active', isActive);
        });

        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('is-active', index === stepIndex);
            indicator.classList.toggle('is-complete', index < stepIndex);
            indicator.setAttribute('aria-current', index === stepIndex ? 'step' : 'false');
        });

        const totalSteps = panels.length || 1;
        const percent = Math.round(((stepIndex + 1) / totalSteps) * 100);
        const currentPanel = panels[stepIndex];
        const currentTitle = currentPanel?.dataset.stepTitle || `Paso ${stepIndex + 1}`;

        if (progressHeading) progressHeading.textContent = `Vas por el ${percent}%`;
        if (progressCopy) progressCopy.textContent = stepSummaries[stepIndex] || stepSummaries[0];
        if (progressPercent) progressPercent.textContent = `${percent}%`;
        if (progressBar) progressBar.style.width = `${percent}%`;
        if (progressRing) {
            progressRing.style.background = `radial-gradient(circle at center, #0f1722 0 53%, transparent 55%), conic-gradient(var(--color-secondary) 0 ${percent * 3.6}deg, rgba(194, 198, 212, 0.28) ${percent * 3.6}deg 360deg)`;
        }
        if (currentStepTitle) currentStepTitle.textContent = currentTitle;
        if (currentStepMeta) currentStepMeta.textContent = `Paso ${stepIndex + 1} de ${totalSteps}`;
        if (sidebarTip) sidebarTip.textContent = stepTips[stepIndex] || stepTips[0];
        if (sidebarLoader) {
            sidebarLoader.textContent = stepIndex === 2
                ? 'En este paso comprimimos fotos antes del envío para que la carga sea más rápida.'
                : stepIndex === 3
                    ? 'En este paso validamos la ubicación dentro de Costa Rica y esperamos la respuesta del mapa.'
                    : 'Avanza paso a paso. Aquí verás el estado de carga, compresión o validación.';
        }

        if (scroll) {
            wizard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    wizard.querySelectorAll('[data-wizard-next]').forEach((button) => {
        button.addEventListener('click', () => {
            const currentPanel = panels[stepIndex];
            if (!validateStep(currentPanel)) return;

            autosave.save();
            stepIndex = Math.min(stepIndex + 1, panels.length - 1);
            syncWizard();
        });
    });

    wizard.querySelectorAll('[data-wizard-prev]').forEach((button) => {
        button.addEventListener('click', () => {
            stepIndex = Math.max(stepIndex - 1, 0);
            syncWizard();
        });
    });

    indicators.forEach((indicator) => {
        indicator.addEventListener('click', () => {
            const nextIndex = Number(indicator.dataset.stepTarget || 0);

            if (nextIndex > stepIndex && !validateStep(panels[stepIndex])) {
                return;
            }

            stepIndex = nextIndex;
            syncWizard();
        });
    });

    wizard.addEventListener('submit', async (event) => {
        const submitButton = wizard.querySelector('[data-submit-onboarding]');
        if (!validateStep(panels[stepIndex])) {
            event.preventDefault();
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Preparando fotos y registrando...';
        }

        if (statusNode) {
            statusNode.textContent = 'Comprimiendo im?genes y enviando tu anuncio...';
            statusNode.dataset.state = 'processing';
        }

        const inputs = wizard.querySelectorAll('[data-compress-image]');
        for (const input of inputs) {
            const file = input.files?.[0];
            if (!file) continue;

            const optimizedFile = await compressImageFile(file);
            if (optimizedFile !== file) {
                const transfer = new DataTransfer();
                transfer.items.add(optimizedFile);
                input.files = transfer.files;
            }
        }

        await autosave.save();
        window.setTimeout(() => {
            autosave.clear();
        }, 1200);
    });

    wireModelFiltering(wizard);
    wirePricePreview(wizard);
    wirePhotoHints(wizard, autosave);
    wirePhotoSequence(wizard);
    autosave.restore();
    syncWizard(false);
}

const mapCanvas = document.querySelector('[data-map-canvas]');
const mapSearch = document.querySelector('[data-map-search]');
const sidebarLoader = document.querySelector('[data-sidebar-loader]');
const latInput = document.querySelector('[data-map-lat]');
const lngInput = document.querySelector('[data-map-lng]');
const cityInput = document.querySelector('[data-map-city]');
const stateInput = document.querySelector('[data-map-state]');
const labelInput = document.querySelector('[data-map-label]');
const provinceField = document.querySelector('[data-location-province]');
const cantonField = document.querySelector('[data-location-canton]');
const districtField = document.querySelector('[data-location-district]');
const treeNode = document.getElementById('cr-location-tree');
const locationTree = (() => {
    try {
        return JSON.parse(treeNode?.textContent || '[]');
    } catch (error) {
        return [];
    }
})();

if (provinceField && cantonField && districtField) {
    const normalize = (value) => (value || '').trim().toLowerCase();

    const setOptions = (select, values, placeholder) => {
        if (!select) return;

        const currentValue = select.value;
        select.innerHTML = [`<option value="">${placeholder}</option>`]
            .concat(values.map((value) => `<option value="${value}">${value}</option>`))
            .join('');

        if (values.some((value) => normalize(value) === normalize(currentValue))) {
            select.value = currentValue;
        }
    };

    const getProvince = () => locationTree.find((province) => normalize(province.name) === normalize(provinceField?.value));
    const getCanton = () => getProvince()?.cantons?.find((canton) => normalize(canton.name) === normalize(cantonField?.value));

    const syncLocationSelectors = (source = null) => {
        setOptions(provinceField, locationTree.map((province) => province.name), 'Selecciona una provincia');

        const province = getProvince();
        const cantons = province?.cantons ?? [];
        setOptions(cantonField, cantons.map((canton) => canton.name), province ? 'Selecciona un cantón' : 'Selecciona primero una provincia');
        if (cantonField) {
            cantonField.disabled = !province;
        }

        if (source === 'province' && cantonField) {
            const currentCantonValid = cantons.some((canton) => normalize(canton.name) === normalize(cantonField.value));
            if (!currentCantonValid) {
                cantonField.value = '';
                if (districtField) districtField.value = '';
            }
        }

        const canton = getCanton();
        const districts = canton?.districts ?? [];
        setOptions(districtField, districts, canton ? 'Selecciona un distrito' : 'Selecciona primero un cantón');
        if (districtField) {
            districtField.disabled = !canton;
        }

        if (source === 'canton' && districtField) {
            const currentDistrictValid = districts.some((district) => normalize(district) === normalize(districtField.value));
            if (!currentDistrictValid) {
                districtField.value = '';
            }
        }
    };

    const syncManualLocation = () => {
        if (labelInput && mapSearch) labelInput.value = mapSearch.value || '';
        if (cityInput && districtField) cityInput.value = districtField.value || '';
        if (stateInput && provinceField) stateInput.value = provinceField.value || '';
    };

    mapSearch?.addEventListener('input', syncManualLocation);
    provinceField?.addEventListener('change', () => {
        syncLocationSelectors('province');
        syncManualLocation();
    });
    cantonField?.addEventListener('change', () => {
        syncLocationSelectors('canton');
        syncManualLocation();
    });
    districtField?.addEventListener('change', syncManualLocation);
    syncLocationSelectors();
    syncManualLocation();
    if (!(mapCanvas && mapSearch)) {
        if (sidebarLoader) {
            sidebarLoader.textContent = 'Selecciona provincia, canton y distrito para completar la ubicacion del auto.';
        }
    } else {
        const bootMap = () => {
        if (!window.google?.maps?.places) {
            mapCanvas.innerHTML = '<div class="map-canvas__fallback">Configura `GOOGLE_MAPS_API_KEY` para usar el mapa interactivo. Mientras tanto puedes completar manualmente provincia, cantón, distrito y la referencia del auto.</div>';
            if (sidebarLoader) {
                sidebarLoader.textContent = 'Google Maps no está activo. Completa referencia, provincia, cantón y distrito manualmente para continuar.';
            }
            return;
        }

        const initialLat = Number(latInput?.value || 9.9281);
        const initialLng = Number(lngInput?.value || -84.0907);
        const map = new window.google.maps.Map(mapCanvas, {
            center: { lat: initialLat, lng: initialLng },
            zoom: 8,
            restriction: {
                latLngBounds: {
                    north: 11.3,
                    south: 8.0,
                    west: -86.2,
                    east: -82.2,
                },
                strictBounds: true,
            },
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: false,
        });

        const marker = new window.google.maps.Marker({
            map,
            position: { lat: initialLat, lng: initialLng },
        });

        const syncPlace = (place) => {
            if (!place.geometry?.location) return;

            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            map.setCenter({ lat, lng });
            map.setZoom(15);
            marker.setPosition({ lat, lng });

            if (latInput) latInput.value = String(lat);
            if (lngInput) lngInput.value = String(lng);
            if (labelInput) labelInput.value = place.formatted_address || mapSearch.value;

            let province = '';
            let canton = '';
            let district = '';

            (place.address_components || []).forEach((component) => {
                if (component.types.includes('administrative_area_level_1')) province = component.long_name;
                if (component.types.includes('administrative_area_level_2')) canton = component.long_name;
                if (component.types.includes('locality') || component.types.includes('sublocality_level_1')) district = component.long_name;
            });

            if (provinceField && province) {
                provinceField.value = province;
            }

            syncLocationSelectors('province');

            if (cantonField && canton) {
                cantonField.value = canton;
            }

            syncLocationSelectors('canton');

            if (districtField && district) {
                districtField.value = district;
            }

            if (cityInput) cityInput.value = district || districtField?.value || '';
            if (stateInput) stateInput.value = province || provinceField?.value || '';
            mapSearch.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const autocomplete = new window.google.maps.places.Autocomplete(mapSearch, {
            fields: ['geometry', 'formatted_address', 'address_components'],
            componentRestrictions: { country: 'cr' },
        });

        autocomplete.addListener('place_changed', () => {
            if (sidebarLoader) {
                sidebarLoader.textContent = 'Ubicación validada dentro de Costa Rica.';
            }
            syncPlace(autocomplete.getPlace());
        });

        if (sidebarLoader) {
            sidebarLoader.textContent = 'Mapa cargado. Busca la dirección exacta del auto dentro de Costa Rica.';
        }
    };
        if (window.google?.maps?.places) {
            bootMap();
        } else {
            let attempts = 0;
            const interval = window.setInterval(() => {
                attempts += 1;
                if (window.google?.maps?.places || attempts > 40) {
                    window.clearInterval(interval);
                    bootMap();
                }
            }, 400);
        }
    }
}
const sellerMakeSelect = document.querySelector('[data-seller-make-select]');
const sellerModelSelect = document.querySelector('[data-seller-model-select]');

if (sellerMakeSelect && sellerModelSelect) {
    const modelOptions = Array.from(sellerModelSelect.querySelectorAll('option[data-make-id]'));

    const syncSellerModels = () => {
        const selectedMake = sellerMakeSelect.value;
        let selectedVisible = false;

        modelOptions.forEach((option) => {
            const visible = !selectedMake || option.dataset.makeId === selectedMake;
            option.hidden = !visible;
            option.disabled = !visible;

            if (visible && option.value === sellerModelSelect.value) {
                selectedVisible = true;
            }
        });

        if (!selectedVisible) {
            sellerModelSelect.value = '';
        }
    };

    sellerMakeSelect.addEventListener('change', syncSellerModels);
    syncSellerModels();
}
