# 555xch Login Theme Reference

Read this file before making any visual change to the app. The login page should stay aligned with the current 555 Results admin login style shown at `https://result.555xch.pro/admin/login`.

## Brand Direction

- Match the existing 555 Results Administration login page.
- Visual tone: dark admin surface, yellow/gold brand heading and labels, amber login action.
- The page should feel simple, direct, and familiar to existing 555xch users.

## Color Tokens

- Page dark: `#111111`
- Panel dark: `#171717`, `#242424`
- Brown glow: `#331204`
- Border gold/brown: `#9A6506`, `#8A5B06`
- Heading/label yellow: `#FFCC00`
- Button amber: `#E69100`
- Placeholder/subtitle blue-grey: `#ADC2DF`
- Text primary: `#FFFFFF`

Tailwind aliases for the broader premium palette are available under `xch.*` in `tailwind.config.cjs`.

## Layout Rules

- Full-screen centered login panel.
- Desktop/reference width should stay near `558px`.
- Panel uses a subtle diagonal dark/brown gradient and a thin gold-brown border.
- Keep generous spacing: large title, subtitle, labelled fields, and a large login button.
- Mobile should retain the same visual language with narrower padding and touch-friendly controls.

## Component Rules

- Title: `555xch`, bold, yellow.
- Do not show a subtitle/tagline below the login title.
- Labels: yellow, bold.
- Inputs: dark grey surface, gold-brown border, blue-grey placeholders, yellow focus state.
- Captcha: simple plus/minus math only. Subtraction must always show a non-negative answer by placing the larger number first.
- Primary CTA: solid amber button with white bold text. Do not use a generic admin red/blue button.
- Do not show secondary navigation on the login screen unless explicitly requested.
- Motion should be minimal: a short fade/slide on page load and a small press state on the button.

## Implementation Notes

- Current implementation: `src/pages/Login.jsx`.
- Scoped background/panel helpers: `src/index.css` under `Result admin login theme`.
- Keep future login edits consistent with this README unless the brand direction is intentionally changed.
