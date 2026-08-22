// Emoji tematico por categoria de estabelecimento -- usado tanto no filtro rapido do Discover
// quanto nos marcadores do mapa (RestaurantMap), pra manter a mesma linguagem visual nos dois.
const EMOJI_BY_CATEGORY_SLUG = {
    restaurante: '🍽️',
    bar: '🍺',
    hamburgueria: '🍔',
    pizzaria: '🍕',
    cafeteria: '☕',
    padaria: '🥖',
    lanchonete: '🥪',
    sorveteria: '🍦',
    acai: '🥣',
    churrascaria: '🍖',
    japones: '🍣',
};

const DEFAULT_EMOJI = '🍽️';

/**
 * @param {Array<{slug?: string}>|undefined} categories
 */
export function emojiForRestaurant(categories) {
    for (const category of categories ?? []) {
        const emoji = EMOJI_BY_CATEGORY_SLUG[category?.slug];
        if (emoji) return emoji;
    }

    return DEFAULT_EMOJI;
}

export function emojiForCategorySlug(slug) {
    return EMOJI_BY_CATEGORY_SLUG[slug] ?? DEFAULT_EMOJI;
}
