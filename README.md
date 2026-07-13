# XCH555 React App

Current application source for the XCH555 React/Vite Progressive Web App.

## Development

```powershell
npm install
npm run dev
```

## Production build

```powershell
npm run build
npm run preview
```

The web frontend lives in `src/`. It uses `VITE_API_ROOT` when configured and otherwise calls `/index.php/api/v1`.

The production build includes a web app manifest and service worker. After deployment over HTTPS, users can install the app directly from their browser without an APK.

Legacy ASP.NET, PHP pages, migration material, unused theme assets, and the former Expo/APK wrapper are preserved under `dump/`.
