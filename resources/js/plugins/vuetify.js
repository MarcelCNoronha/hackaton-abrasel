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

// Same warm food palette as the marketing landing page (resources/views/landing.blade.php),
// used only by the public/guest experience (PublicLayout, GuestLayout and their pages) via
// useTheme().global.name — kept separate from the default theme above, which the authenticated
// dashboards (Owner/Admin, still plain Tailwind chrome) still rely on, so switching this one
// never touches them.
const vicosaFoodDark = {
    dark: true,
    colors: {
        background: '#120B08',
        surface: '#1C120C',
        primary: '#F97316',
        'primary-darken-1': '#C2410C',
        secondary: '#22C55E',
        'secondary-darken-1': '#16A34A',
        accent: '#FBBF24',
        error: '#EF4444',
        info: '#60A5FA',
        success: '#22C55E',
        warning: '#FBBF24',
        'on-background': '#FDF6F0',
        'on-surface': '#FDF6F0',
    },
    variables: {
        'border-color': '#F97316',
        'border-opacity': 0.24,
    },
};

export default createVuetify({
    theme: {
        defaultTheme: 'vicosaFoodTheme',
        themes: { vicosaFoodTheme, vicosaFoodDark },
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
