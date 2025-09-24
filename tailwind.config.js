/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './includes/**/*.php',
    './widgets/**/*.php',
    './assets/js/**/*.js',
    './templates/**/*.php',
    './**/*.php', // برای اطمینان از اسکن تمام فایل‌های PHP
  ],
  theme: {
    extend: {
      animation: {
        'fade-in': 'fadeIn 0.5s ease-out',
        shake: 'shake 0.3s ease-in-out',
        'progress-fill': 'progressFill 0.5s ease-in-out',
        price: 'fadeIn 0.3s ease-out',
        message: 'fadeIn 0.3s ease-out',
      },
      keyframes: {
        fadeIn: {
          '0%': { opacity: 0, transform: 'translateY(10px)' },
          '100%': { opacity: 1, transform: 'translateY(0)' },
        },
        shake: {
          '0%, 100%': { transform: 'translateX(0)' },
          '25%': { transform: 'translateX(-5px)' },
          '75%': { transform: 'translateX(5px)' },
        },
        progressFill: {
          '0%': { width: '0%' },
          '100%': { width: '100%' },
        },
      },
    },
  },
  plugins: [
    require('tailwindcss-rtl'), // استفاده از پلاگین صحیح برای RTL
  ],
  direction: {
    'rtl': 'rtl',
  },
};