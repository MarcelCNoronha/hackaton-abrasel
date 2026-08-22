export function formatBRL(value) {
    return `R$ ${Number(value).toFixed(2).replace('.', ',')}`;
}
