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

const createAutosaveManager = (form, statusNode) => {
    const storageKey = form.dataset.autosaveKey;

    if (!storageKey || !('localStorage' in window)) {
        return {
            restore: () => {},
            save: () => {},
            clear: () => {},
            stamp: () => {},
        };
    }

    const setStatus = (message, tone = 'idle') => {
        if (!statusNode) return;
        statusNode.textContent = message;
        statusNode.dataset.state = tone;
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
                return;
            }

            payload[field.name] = field.value;
        });

        return payload;
    };

    const restore = () => {
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

            if (draft.savedAt) {
                setStatus(`Borrador restaurado. Ultimo guardado ${draft.savedAt}.`, 'restored');
            }
        } catch {
            setStatus('No se pudo restaurar el borrador local.', 'error');
        }
    };

    let timer = null;

    const save = () => {
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
        timer = window.setTimeout(save, 250);
    };

    const clear = () => {
        window.localStorage.removeItem(storageKey);
        setStatus('Borrador local limpiado porque el envio termino.', 'done');
    };

    form.addEventListener('input', stamp);
    form.addEventListener('change', stamp);

    return { restore, save, clear, stamp };
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

const wirePhotoHints = (wizard) => {
    const photoInputs = wizard.querySelectorAll('[data-photo-input]');
    const sidebarLoader = document.querySelector('[data-sidebar-loader]');

    photoInputs.forEach((input) => {
        const card = input.closest('.seller-photo-card');
        const hint = card?.querySelector('[data-file-hint]');

        if (card && !card.querySelector('[data-photo-preview]')) {
            const preview = document.createElement('div');
            preview.className = 'seller-photo-preview';
            preview.dataset.photoPreview = 'true';
            preview.innerHTML = '<span class="material-symbols-outlined">image</span><strong>Vista previa</strong><small>Aun no has seleccionado una foto.</small>';
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
                    preview.innerHTML = '<span class="material-symbols-outlined">image</span><strong>Vista previa</strong><small>Aun no has seleccionado una foto.</small>';
                }
                return;
            }

            card.classList.add('is-processing');
            if (hint) {
                hint.textContent = `Preparando ${file.name}...`;
            }
            if (sidebarLoader) {
                sidebarLoader.textContent = `Comprimiendo ${file.name} para que suba mas rapido...`;
            }

            const optimizedFile = await compressImageFile(file);
            const transfer = new DataTransfer();
            transfer.items.add(optimizedFile);
            input.files = transfer.files;

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
                sidebarLoader.textContent = `${optimizedFile.name} quedo listo. Puedes seguir agregando fotos.`;
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
            preview.textContent = 'El precio oficial se mostrara en colones. Debajo veras una referencia pequena en dolares.';
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
    const requiredPanels = panels.filter((panel) => panel.dataset.photoOptional !== 'true');
    const optionalPanels = panels.filter((panel) => panel.dataset.photoOptional === 'true');

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
            sidebarLoader.textContent = `Sube ${title.toLowerCase()} y luego continua con la siguiente fotografia.`;
        }
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
            showOptionalEntry('Tus fotos opcionales tambien quedaron listas. Ya puedes pasar al siguiente paso.');
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
        'Usa el nombre comercial correcto de la version y una descripcion breve pero confiable.',
        'Completa kilometraje, motor, combustible y precio en colones con datos exactos.',
        'Las fotos frontal, trasera, laterales e interiores suelen acelerar los contactos.',
        'Busca el punto exacto del auto dentro de Costa Rica para ubicarlo mejor.',
        'Cierra con correo o telefono y podras manejar este y otros autos desde tu panel.',
    ];
    const stepSummaries = [
        'Completa los datos base del auto para arrancar bien tu publicacion.',
        'Normaliza precio, mecanica y condicion para que el anuncio sea comparable.',
        'Agrega extras y fotos claras para elevar confianza y conversion.',
        'Confirma la ubicacion exacta del auto dentro de Costa Rica.',
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
            progressRing.style.background = `radial-gradient(circle at center, white 54%, transparent 56%), conic-gradient(var(--color-secondary) 0 ${percent * 3.6}deg, rgba(194, 198, 212, 0.45) ${percent * 3.6}deg 360deg)`;
        }
        if (currentStepTitle) currentStepTitle.textContent = currentTitle;
        if (currentStepMeta) currentStepMeta.textContent = `Paso ${stepIndex + 1} de ${totalSteps}`;
        if (sidebarTip) sidebarTip.textContent = stepTips[stepIndex] || stepTips[0];
        if (sidebarLoader) {
            sidebarLoader.textContent = stepIndex === 2
                ? 'En este paso comprimimos fotos antes del envio para que la carga sea mas rapida.'
                : stepIndex === 3
                    ? 'En este paso validamos la ubicacion dentro de Costa Rica y esperamos la respuesta del mapa.'
                    : 'Avanza paso a paso. Aqui veras el estado de carga, compresion o validacion.';
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
            statusNode.textContent = 'Comprimiendo imagenes y enviando tu anuncio...';
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

        autosave.save();
        window.setTimeout(() => autosave.clear(), 1200);
    });

    autosave.restore();
    wireModelFiltering(wizard);
    wirePricePreview(wizard);
    wirePhotoHints(wizard);
    wirePhotoSequence(wizard);
    syncWizard(false);
}

const mapCanvas = document.querySelector('[data-map-canvas]');
const mapSearch = document.querySelector('[data-map-search]');

if (mapCanvas && mapSearch) {
    const bootMap = () => {
        const sidebarLoader = document.querySelector('[data-sidebar-loader]');
        if (!window.google?.maps?.places) {
            mapCanvas.innerHTML = '<div class="map-canvas__fallback">Configura GOOGLE_MAPS_API_KEY para usar el mapa interactivo. Mientras tanto puedes escribir la ubicacion manualmente en el buscador.</div>';
            if (sidebarLoader) {
                sidebarLoader.textContent = 'El mapa no cargo. Puedes seguir con la busqueda manual mientras configuras Google Maps.';
            }
            return;
        }

        const latInput = document.querySelector('[data-map-lat]');
        const lngInput = document.querySelector('[data-map-lng]');
        const cityInput = document.querySelector('[data-map-city]');
        const stateInput = document.querySelector('[data-map-state]');
        const labelInput = document.querySelector('[data-map-label]');
        const cityDisplay = document.querySelector('[data-map-city-display]');
        const stateDisplay = document.querySelector('[data-map-state-display]');

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

            let city = 'Costa Rica';
            let state = 'Costa Rica';

            (place.address_components || []).forEach((component) => {
                if (component.types.includes('locality')) city = component.long_name;
                if (component.types.includes('administrative_area_level_1')) state = component.long_name;
            });

            if (cityInput) cityInput.value = city;
            if (stateInput) stateInput.value = state;
            if (cityDisplay) cityDisplay.value = city;
            if (stateDisplay) stateDisplay.value = state;
            mapSearch.dispatchEvent(new Event('input', { bubbles: true }));
        };

        const autocomplete = new window.google.maps.places.Autocomplete(mapSearch, {
            fields: ['geometry', 'formatted_address', 'address_components'],
            componentRestrictions: { country: 'cr' },
        });

        autocomplete.addListener('place_changed', () => {
            if (sidebarLoader) {
                sidebarLoader.textContent = 'Ubicacion validada dentro de Costa Rica.';
            }
            syncPlace(autocomplete.getPlace());
        });

        if (sidebarLoader) {
            sidebarLoader.textContent = 'Mapa cargado. Busca la direccion exacta del auto dentro de Costa Rica.';
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
