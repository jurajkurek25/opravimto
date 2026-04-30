<!DOCTYPE html>
<html class="dark" lang="sk">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= e($pageTitle ?? 'opravimto.sk') ?> – Profesionálna klinika elektroniky</title>
<meta name="description" content="Rýchle a spoľahlivé opravy smartfónov, notebookov, tabletov a herných konzol. Transparentné ceny, certifikovaní technici."/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="/assets/css/app.css" rel="stylesheet"/>
<script>
tailwind.config = {
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary":                "#72d6d8",
                "on-primary":             "#003738",
                "primary-container":      "#339fa1",
                "on-primary-container":   "#002f30",
                "primary-fixed":          "#8ff3f4",
                "primary-fixed-dim":      "#72d6d8",
                "secondary":              "#ffb964",
                "on-secondary":           "#482a00",
                "secondary-container":    "#cc8004",
                "secondary-fixed":        "#ffddba",
                "secondary-fixed-dim":    "#ffb964",
                "tertiary":               "#ffb4aa",
                "tertiary-container":     "#f95b4c",
                "error":                  "#ffb4ab",
                "error-container":        "#93000a",
                "on-error-container":     "#ffdad6",
                "background":             "#161216",
                "on-background":          "#e8e0e6",
                "surface":                "#161216",
                "surface-dim":            "#161216",
                "surface-bright":         "#3c383d",
                "surface-variant":        "#383338",
                "surface-container-lowest":"#100d11",
                "surface-container-low":  "#1e1a1f",
                "surface-container":      "#221e23",
                "surface-container-high": "#2d292d",
                "surface-container-highest":"#383338",
                "on-surface":             "#e8e0e6",
                "on-surface-variant":     "#bdc9c9",
                "outline":                "#879393",
                "outline-variant":        "#3d4949",
                "inverse-surface":        "#e8e0e6",
                "inverse-on-surface":     "#332f34",
                "inverse-primary":        "#00696b",
                "surface-tint":           "#72d6d8",
                "cta":                    "#EC9A29",
                "cta-dark":               "#2b1700",
            },
            fontFamily: { sans: ["Inter", "sans-serif"] },
            borderRadius: {
                DEFAULT: "0.5rem",
                lg: "1rem",
                xl: "1.5rem",
                full: "9999px",
            },
        }
    }
}
</script>
</head>
<body class="bg-background text-on-background font-sans antialiased min-h-screen flex flex-col">
