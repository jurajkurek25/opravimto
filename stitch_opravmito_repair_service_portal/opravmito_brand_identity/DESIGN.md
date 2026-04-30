---
name: Opravmito Brand Identity
colors:
  surface: '#161216'
  surface-dim: '#161216'
  surface-bright: '#3c383d'
  surface-container-lowest: '#100d11'
  surface-container-low: '#1e1a1f'
  surface-container: '#221e23'
  surface-container-high: '#2d292d'
  surface-container-highest: '#383338'
  on-surface: '#e8e0e6'
  on-surface-variant: '#bdc9c9'
  inverse-surface: '#e8e0e6'
  inverse-on-surface: '#332f34'
  outline: '#879393'
  outline-variant: '#3d4949'
  surface-tint: '#72d6d8'
  primary: '#72d6d8'
  on-primary: '#003738'
  primary-container: '#339fa1'
  on-primary-container: '#002f30'
  inverse-primary: '#00696b'
  secondary: '#ffb964'
  on-secondary: '#482a00'
  secondary-container: '#cc8004'
  on-secondary-container: '#3e2400'
  tertiary: '#ffb4aa'
  on-tertiary: '#690003'
  tertiary-container: '#f95b4c'
  on-tertiary-container: '#5c0002'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#8ff3f4'
  primary-fixed-dim: '#72d6d8'
  on-primary-fixed: '#002020'
  on-primary-fixed-variant: '#004f51'
  secondary-fixed: '#ffddba'
  secondary-fixed-dim: '#ffb964'
  on-secondary-fixed: '#2b1700'
  on-secondary-fixed-variant: '#663e00'
  tertiary-fixed: '#ffdad5'
  tertiary-fixed-dim: '#ffb4aa'
  on-tertiary-fixed: '#410001'
  on-tertiary-fixed-variant: '#910b0b'
  background: '#161216'
  on-background: '#e8e0e6'
  surface-variant: '#383338'
typography:
  h1:
    fontFamily: Inter
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  h2:
    fontFamily: Inter
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  h3:
    fontFamily: Inter
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
    letterSpacing: '0'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
    letterSpacing: '0'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
    letterSpacing: '0'
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.2'
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1.1'
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 24px
  margin: 32px
---

## Brand & Style

The brand personality for this design system is **technical, reliable, and precise**. It aims to instill immediate confidence in customers handing over expensive electronics for repair. The visual style follows a **Corporate / Modern** aesthetic, utilizing a deep color palette that feels engineered rather than purely decorative.

The interface prioritizes clarity and functional density, ensuring that technical specifications and repair statuses are easily digestible. The emotional response is one of professional competence—positioning the service as a premium, high-tech clinic for hardware.

## Colors

The color strategy for this design system utilizes a high-contrast dark mode foundation. The **Deep Dark Teal** background provides a sophisticated, low-strain canvas. 

- **Primary Teal (#0F8B8D):** Used for structural accents, active states, and brand-related iconography. It represents the "technical" side of the brand.
- **Accent Orange (#EC9A29):** Reserved exclusively for Call to Action (CTA) elements, conversion points, and critical highlights to ensure high visibility against the teal backdrop.
- **Alert Red (#A8201A):** Used for urgent status updates, errors, or priority repair indicators.
- **Light Gray (#DAD2D8):** The primary color for text and secondary interface elements, chosen for its high legibility and soft contrast against the dark background.

## Typography

This design system uses **Inter** as its sole typeface. Inter’s tall x-height and geometric clarity make it ideal for a technical e-commerce site where users must read complex price breakdowns and service descriptions.

Headlines use tighter letter spacing and heavier weights to command authority. Body text is optimized for readability with a generous line height. Labels utilize increased letter spacing and uppercase styling for "Meta" information, such as SKU numbers or device categories, to create a clear distinction from narrative content.

## Layout & Spacing

The layout is built on a **12-column fluid grid system** with a maximum container width of 1280px. The spacing rhythm is based on a **8px base unit**, ensuring mathematical harmony across all components.

- **Gutters:** 24px between columns to allow for breathing room between technical cards.
- **Margins:** A minimum of 32px on the outer edges of the viewport for mobile and tablet, increasing to 64px+ on large desktops.
- **Vertical Rhythm:** Components are spaced using multiples of 8px (24px, 48px, 80px) to maintain a clean, structured vertical flow.

## Elevation & Depth

In this dark-mode environment, depth is communicated through **Tonal Layering** and **Low-Contrast Outlines** rather than heavy shadows.

- **Surface Levels:** The base background is the lowest level. Cards and containers use a slightly lighter teal (`#1A4554`) to appear closer to the user. Hover states use a secondary elevation tier (`#225566`).
- **Outlines:** To define boundaries without visual clutter, elements utilize 1px solid borders in a semi-transparent light gray (10-15% opacity).
- **Subtle Glows:** For the Orange CTA buttons, a soft, diffused outer glow using the primary orange color (15% opacity) may be applied on hover to simulate a "powered-on" electronic state.

## Shapes

The shape language for this design system strikes a balance between "industrial" and "friendly." 

- **Components:** Buttons and input fields use a **8px (0.5rem)** corner radius, providing a professional, modern feel.
- **Cards:** Larger containers like product cards or repair tracking modules use a **16px (1rem)** radius to soften the technical layout.
- **Icons:** Icons should feature slightly rounded terminals to match the font and component geometry.

## Components

### Buttons
- **Primary (CTA):** Solid Orange (#EC9A29) with dark text. High emphasis.
- **Secondary:** Outlined Teal (#0F8B8D) with teal text. Used for "Learn More" or "View Specs."
- **Ghost:** Light Gray text with no background. Used for navigation or utility actions.

### Status Indicators (Repair Badges)
- Use a **Chip** component with a low-opacity background and high-intensity text/border.
- *Pending:* Orange tint.
- *In Progress:* Teal tint.
- *Completed:* Green tint.
- *Urgent/Problem:* Red tint.

### Input Fields
- Dark teal background (#1A4554) with a subtle 1px border. 
- Active states should transition the border to the primary Teal (#0F8B8D).
- Labels should sit above the input in the `label-md` style.

### Service Cards
- Used for selecting device types (e.g., iPhone Repair, MacBook Repair).
- Features a large icon/image, a clear headline, and a starting price at the bottom right in the Accent Orange to draw the eye.

### Progress Tracker
- A horizontal stepper for tracking repairs. Completed steps are filled with Primary Teal; the current step pulses subtly; future steps are semi-transparent Light Gray.