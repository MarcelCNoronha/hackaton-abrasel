import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';

const vicosaFoodTheme = {
    dark: false,
    colors: {
        background: '#FAF7F2',
        surface: '#FFFFFF',
        primary: '#E15241',
        'primary-darken-1': '#B93E30',
        secondary: '#1F6E43',
        'secondary-darken-1': '#16512F',
        accent: '#F2A93B',
        error: '#D64545',
        info: '#2F6FED',
        success: '#1F6E43',
        warning: '#F2A93B',
        'on-background': '#241C1A',
        'on-surface': '#241C1A',
    },
    variables: {
        'border-color': '#241C1A',
        'border-opacity': 0.08,
    },
};

export default createVuetify({
    theme: {
        defaultTheme: 'vicosaFoodTheme',
        themes: { vicosaFoodTheme },
    },
    defaults: {
        VBtn: {
            rounded: 'lg',
            fontWeight: 600,
        },
        VCard: {
            rounded: 'lg',
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
            rounded: 'lg',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
            rounded: 'lg',
        },
        VChip: {
            rounded: 'lg',
        },
        VAlert: {
            rounded: 'lg',
        },
    },
});
