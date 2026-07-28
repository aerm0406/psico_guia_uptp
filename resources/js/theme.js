// Sistema de temas
class ThemeManager {
    constructor() {
        this.themes = {
            light: 'light',
            dark: 'dark',
            auto: 'auto'
        };
        this.currentTheme = this.getStoredTheme() || this.themes.auto;
        this.init();
    }

    // Obtener tema guardado en localStorage
    getStoredTheme() {
        return localStorage.getItem('theme');
    }

    // Guardar tema en localStorage
    saveTheme(theme) {
        localStorage.setItem('theme', theme);
        this.currentTheme = theme;
    }

    // Detectar tema del sistema
    getSystemTheme() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    // Aplicar tema al documento
    applyTheme(theme) {
        let activeTheme = theme;

        if (theme === this.themes.auto) {
            activeTheme = this.getSystemTheme();
        }

        // Aplicar data attribute al HTML
        document.documentElement.setAttribute('data-theme', activeTheme);

        // Guardar tema actual para referencia
        document.documentElement.setAttribute('data-user-theme', theme);

        // Dispatch evento para que otros componentes se enteren
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: activeTheme, userTheme: theme }
        }));
    }

    // Cambiar tema
    setTheme(theme) {
        if (!this.themes[theme]) return;

        this.saveTheme(theme);
        this.applyTheme(theme);

        // Actualizar UI del selector
        this.updateSelectorUI(theme);
    }

    // Escuchar cambios del sistema
    listenToSystemTheme() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.currentTheme === this.themes.auto) {
                const newTheme = e.matches ? 'dark' : 'light';
                this.applyTheme(this.themes.auto);
            }
        });
    }

    // Inicializar
    init() {
        this.applyTheme(this.currentTheme);
        this.listenToSystemTheme();
    }

    // Actualizar UI del selector de temas
    updateSelectorUI(selectedTheme) {
        // Actualizar botones activos
        document.querySelectorAll('[data-theme]').forEach(btn => {
            const themeValue = btn.getAttribute('data-theme');
            if (themeValue === selectedTheme) {
                btn.classList.add('active', 'bg-blue-50', 'text-blue-600');
                btn.classList.remove('text-gray-700', 'hover:bg-gray-50');
            } else {
                btn.classList.remove('active', 'bg-blue-50', 'text-blue-600');
                btn.classList.add('text-gray-700', 'hover:bg-gray-50');
            }
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.themeManager = new ThemeManager();
});

// Exponer funciones globales para usar en Alpine.js o eventos onclick
window.setTheme = (theme) => {
    if (window.themeManager) {
        window.themeManager.setTheme(theme);
    }
};

window.getCurrentTheme = () => {
    return window.themeManager ? window.themeManager.currentTheme : 'auto';
};
