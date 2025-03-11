module.exports = {
  content: [
    "./src/**/*.{vue,js,ts,jsx,tsx}",
    "./public/index.html",
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
}