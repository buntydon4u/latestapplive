/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        xch: {
          black: '#050505',
          ink: '#0A0A0A',
          surface: '#111111',
          gold: '#F5B301',
          gold2: '#D89B00',
          orange: '#FF5A00',
          text: '#FFFFFF',
          muted: '#D0D0D0',
          dim: '#8A8A8A'
        }
      },
      boxShadow: {
        'xch-gold': '0 16px 38px rgba(245,179,1,0.28), 0 0 34px rgba(255,90,0,0.16)'
      }
    }
  },
  plugins: []
};
