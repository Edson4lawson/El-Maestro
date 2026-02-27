/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // Palette Ultra-Premium (M pour Maestro)
        m: {
          gold: '#D4AF37',       // Champagne Gold
          obsidian: '#0A0A0A',   // Noir Obsidian
          ebony: '#161616',      // Ébène texturé
          accent: '#C5A028',     // Or poli
          linen: '#F8F5F2',      // Ivoire / Lin
          bronze: '#A67C00',     // Pour le mode clair
          stone: '#1C1917',      // Texte sombre chaud
        },
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        display: ['Outfit', 'sans-serif'],
      },
      backgroundImage: {
        'premium-gradient': 'linear-gradient(135deg, #D4AF37 0%, #C5A028 50%, #B8860B 100%)',
        'dark-glass': 'radial-gradient(circle at top right, rgba(212, 175, 55, 0.05), transparent)',
      },
      animation: {
        'float': 'float 6s ease-in-out infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-20px)' },
        }
      }
    },
  },
  plugins: [],
}

