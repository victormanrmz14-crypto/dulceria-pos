// Utilidades de formato compartidas

export const moneda = (n) =>
    '$' + Number(n ?? 0).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

export const numero = (n) => Number(n ?? 0).toLocaleString('es-MX');
