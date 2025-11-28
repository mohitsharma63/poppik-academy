# Poppik Academy Website

## Overview

A professional single-page website for Poppik Academy - a modern, skill-focused training institution dedicated to the Beauty, Lifestyle, and Wellness industries.

## Tech Stack

- **Framework**: Angular 17+ (Standalone Components)
- **Styling**: Custom CSS with CSS Variables
- **Fonts**: Playfair Display + Poppins (Google Fonts)
- **Build Tool**: Angular CLI with Vite

## Project Structure

```
src/
  app/
    app.component.ts      # Main component logic
    app.component.html    # Template with all sections
    app.component.css     # Component styles
    app.config.ts         # Application configuration
    app.routes.ts         # Routes configuration
  styles.css              # Global styles and design system
  index.html              # Entry HTML
```

## Website Sections

1. **Home** - Hero section with animated slider (4 slides)
2. **About Us** - Mission, Vision, and Why Choose Us
3. **Courses** - 9 courses in Beauty, Lifestyle, and Wellness
4. **Gallery** - Image grid showcase
5. **Highlights** - 7 advantage cards
6. **Job Placement** - Career support features and partner logos
7. **Careers** - Career paths across three industries
8. **Blog** - Latest blog posts with categories
9. **Video Hub** - Tutorial videos
10. **Certificate Verification** - Form to verify certificates
11. **Contact** - Contact form and information
12. **Footer** - Quick links and policies

## Design System

### Colors
- Primary: `#D4A574` (Gold accent)
- Secondary: `#2C3E50` (Dark blue)
- Accent: `#E8D5C4` (Light cream)
- Background: `#FAF8F5` (Warm white)

### Typography
- Headings: Playfair Display (serif)
- Body: Poppins (sans-serif)

## Development

### Run Development Server
```bash
npx ng serve --host 0.0.0.0 --port 3000 --disable-host-check
```

### Build for Production
```bash
npx ng build --configuration production
```

## Recent Changes

- **Nov 27, 2025**: Initial website creation with all 12 sections
- Disabled SSR for development mode for faster builds
- Implemented responsive design for all screen sizes

## Contact Information

- **Grievance Officer**: Hanmnt Dadas
- **Email**: hanmnt@poppik.in
- **Phone**: +91-7039011291
