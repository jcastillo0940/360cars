import React, { useState } from 'react';
import {
    siAcura,
    siAudi,
    siBmw,
    siCadillac,
    siChevrolet,
    siChrysler,
    siCitroen,
    siFiat,
    siFord,
    siHonda,
    siHyundai,
    siInfiniti,
    siJeep,
    siKia,
    siMahindra,
    siMazda,
    siMg,
    siMini,
    siMitsubishi,
    siNissan,
    siOpel,
    siPeugeot,
    siPorsche,
    siRam,
    siRenault,
    siSeat,
    siSkoda,
    siSubaru,
    siSuzuki,
    siTesla,
    siToyota,
    siVolkswagen,
    siVolvo,
} from 'simple-icons';

const localBrandIcons = {
    Acura: siAcura,
    Audi: siAudi,
    BMW: siBmw,
    Cadillac: siCadillac,
    Chevrolet: siChevrolet,
    Chrysler: siChrysler,
    Citroen: siCitroen,
    Fiat: siFiat,
    Ford: siFord,
    Honda: siHonda,
    Hyundai: siHyundai,
    Infiniti: siInfiniti,
    Jeep: siJeep,
    Kia: siKia,
    Mahindra: siMahindra,
    Mazda: siMazda,
    MG: siMg,
    Mini: siMini,
    Mitsubishi: siMitsubishi,
    Nissan: siNissan,
    Opel: siOpel,
    Peugeot: siPeugeot,
    Porsche: siPorsche,
    RAM: siRam,
    Renault: siRenault,
    Seat: siSeat,
    Skoda: siSkoda,
    Subaru: siSubaru,
    Suzuki: siSuzuki,
    Tesla: siTesla,
    Toyota: siToyota,
    Volkswagen: siVolkswagen,
    Volvo: siVolvo,
};

function iconColor(icon) {
    if (!icon?.hex) {
        return '#1e3a8a';
    }

    const hex = `#${icon.hex}`;

    return hex.toLowerCase() === '#ffffff' ? '#0f172a' : hex;
}

function brandSlug(name = '') {
    return String(name)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

export function brandLogoUrl(name) {
    return `/logos/${brandSlug(name)}.png`;
}

export function BrandMark({ name, className = '' }) {
    const icon = localBrandIcons[name];
    const [useImage, setUseImage] = useState(true);
    const initials = name
        .split(/\s+/)
        .map((chunk) => chunk[0] || '')
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <div className={`inline-flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl bg-slate-50 p-2 font-headline text-lg font-black text-primary ${className}`}>
            {useImage ? (
                <img
                    src={brandLogoUrl(name)}
                    alt={name}
                    className="h-full w-full object-contain"
                    loading="lazy"
                    onError={() => setUseImage(false)}
                />
            ) : icon ? (
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    className="h-full w-full"
                    fill={iconColor(icon)}
                >
                    <path d={icon.path} />
                </svg>
            ) : (
                <span className="opacity-40">{initials}</span>
            )}
        </div>
    );
}
