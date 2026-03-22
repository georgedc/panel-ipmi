# Themes

Each theme lives under `app/Views/themes/<theme-name>/`.

Current active theme is controlled by `APP_THEME` in the environment or `.env`.

Example:
- `APP_THEME=default`

Resolution order:
1. `app/Views/themes/<APP_THEME>/...`
2. the active theme must provide every rendered MVC view

This lets you ship new looks without changing controllers or services.
