import re

with open('themes/ayur-wellness/style.css', 'r', encoding='utf-8') as f:
    css = f.read()

# 1. Update Base / Reset
old_base = """html {
    scroll-behavior: smooth;
    -webkit-text-size-adjust: 100%;
}

body {
    font-family: var(--font-body);
    font-size: 16px;
    line-height: 1.6;
    color: var(--color-charcoal);
    background-color: var(--color-ivory);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    overflow-x: hidden;
}"""

new_base = """html {
    width: 100%;
    max-width: 100%;
    overflow-x: clip;
    scroll-behavior: smooth;
    -webkit-text-size-adjust: 100%;
    box-sizing: border-box;
}

body {
    width: 100%;
    max-width: 100%;
    margin: 0;
    padding: 0;
    overflow-x: clip;
    position: relative;
    font-family: var(--font-body);
    font-size: 15px;
    line-height: 1.6;
    color: var(--color-charcoal);
    background-color: var(--color-ivory);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    box-sizing: border-box;
}

#page, #primary, .site-main, .ayur-homepage,
.ayur-header, .ayur-announcement-bar, .ayur-hero,
.ayur-trust-rail, .ayur-section, .ayur-footer {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    margin-left: 0;
    margin-right: 0;
}"""

if old_base in css:
    css = css.replace(old_base, new_base, 1)
    print("Replaced base reset")
else:
    print("Could not find old_base!")

# 2. Update Container
old_container = """.ayur-container {
    width: 100%;
    max-width: 1240px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 36px;
    padding-right: 36px;
}"""

new_container = """.ayur-container {
    width: 100%;
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding-left: 16px;
    padding-right: 16px;
    box-sizing: border-box;
}

@media (min-width: 768px) {
    .ayur-container {
        padding-left: 24px;
        padding-right: 24px;
    }
}

@media (min-width: 1024px) {
    .ayur-container {
        max-width: 1240px;
        padding-left: 36px;
        padding-right: 36px;
    }
}"""

if old_container in css:
    css = css.replace(old_container, new_container, 1)
    print("Replaced container")
else:
    print("Could not find old_container!")

# 3. Update Section 16 Search Modal
old_search_start = "/* ==========================================================================\n   16. Search Modal Overlay\n   ========================================================================== */"
old_footer_start = "/* ==========================================================================\n   17. Footer & Mobile Bottom Navigation (Sections 24 & 28)\n   ========================================================================== */"

s_idx = css.find(old_search_start)
f_idx = css.find(old_footer_start)

if s_idx != -1 and f_idx != -1:
    new_search_section = """/* ==========================================================================
   16. Search Modal Overlay (Phase 12)
   ========================================================================== */
.ayur-search-modal-wrap {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    z-index: 2100;
    visibility: hidden;
    opacity: 0;
    pointer-events: none;
    transition: opacity var(--transition-base), visibility var(--transition-base);
    display: flex;
    flex-direction: column;
}

.ayur-search-modal-wrap.is-open {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;
}

.ayur-search-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(23, 59, 47, 0.50);
    transition: opacity var(--transition-base);
}

.ayur-search-box {
    position: relative;
    z-index: 2;
    background-color: var(--color-white);
    padding: 20px 0 24px 0;
    padding-top: calc(20px + env(safe-area-inset-top, 0px));
    border-bottom: 1px solid var(--color-border);
    box-shadow: var(--shadow-modal);
    transform: translateY(-16px);
    transition: transform var(--transition-smooth);
}

.ayur-search-modal-wrap.is-open .ayur-search-box {
    transform: translateY(0);
}

.ayur-search-bar-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
}

.ayur-search-bar-icon {
    color: var(--color-forest);
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
}

.ayur-search-field-form {
    flex-grow: 1;
}

#ayurSearchInput {
    width: 100%;
    height: 46px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-btn);
    padding: 0 14px;
    font-size: 15px;
    outline: none;
    color: var(--color-forest);
    background-color: var(--color-ivory);
    box-sizing: border-box;
}

#ayurSearchInput:focus {
    border-color: var(--color-forest);
    background-color: var(--color-white);
}

.ayur-search-close-btn {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--color-forest);
    border-radius: var(--radius-btn);
    flex-shrink: 0;
    cursor: pointer;
}

.ayur-search-close-btn:hover {
    background-color: rgba(23, 59, 47, 0.05);
}

.ayur-search-quick-links {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px solid var(--color-border-subtle);
}

.ayur-search-quick-title {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--color-muted);
    text-transform: uppercase;
    display: block;
    margin-bottom: 8px;
}

.ayur-search-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.ayur-search-pills a {
    font-size: 12px;
    padding: 5px 12px;
    background-color: var(--color-stage);
    border-radius: var(--radius-pill);
    color: var(--color-forest);
    text-decoration: none;
    transition: background-color var(--transition-fast);
}

.ayur-search-pills a:hover {
    background-color: var(--color-sand);
}

"""
    css = css[:s_idx] + new_search_section + css[f_idx:]
    print("Replaced Search Modal section")
else:
    print("Could not find Search Modal indexes!")

# 4. Update @media (max-width: 768px) block
m_start = css.find("@media (max-width: 768px)")
r_start = css.find("/* Reduced Motion Support */")

if m_start != -1 and r_start != -1:
    new_mobile_query = """@media (max-width: 768px) {
    /* 1. Global Viewport & Containers (Phase 1) */
    html, body {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: clip !important;
    }

    #page, #primary, .site-main, .ayur-homepage,
    .ayur-header, .ayur-announcement-bar, .ayur-hero,
    .ayur-trust-rail, .ayur-section, .ayur-footer {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .ayur-container {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 16px !important;
        padding-right: 16px !important;
        box-sizing: border-box !important;
    }

    .ayur-section {
        padding-top: 36px;
        padding-bottom: 36px;
        width: 100%;
        box-sizing: border-box;
    }

    .ayur-section-header {
        margin-bottom: 24px;
        text-align: center;
    }

    .ayur-section-eyebrow {
        font-size: 10px;
        letter-spacing: 0.12em;
        margin-bottom: 6px;
    }

    .ayur-section-title {
        font-size: 1.75rem;
        line-height: 1.25;
        margin-bottom: 8px;
    }

    .ayur-section-desc {
        font-size: 13px;
        line-height: 1.55;
    }

    /* 2. Mobile Announcement Bar (Phase 1) */
    .ayur-announcement-bar {
        padding: 6px 12px;
        font-size: 11px;
    }

    .ayur-announcement-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* 3. Mobile Header (Phase 2) */
    .ayur-header {
        position: sticky;
        top: 0;
        width: 100% !important;
        max-width: 100% !important;
        z-index: 1000;
        background-color: var(--color-ivory);
        border-bottom: 1px solid var(--color-border);
        padding-top: env(safe-area-inset-top, 0px);
    }

    .ayur-header-inner {
        height: 56px;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    .ayur-nav-wrap,
    .ayur-desktop-account {
        display: none !important;
    }

    .ayur-mobile-menu-btn {
        display: inline-flex !important;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        color: var(--color-forest);
        flex-shrink: 0;
    }

    .ayur-brand {
        flex: 1;
        text-align: center;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 0;
        text-decoration: none;
    }

    .ayur-brand-name {
        font-size: 21px;
        line-height: 1;
        font-weight: 700;
    }

    .ayur-brand-tagline {
        font-size: 8px;
        margin-top: 2px;
        letter-spacing: 0.18em;
    }

    .ayur-header-actions {
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
        flex-shrink: 0;
    }

    .ayur-icon-btn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--color-forest);
        position: relative;
    }

    .ayur-cart-badge {
        position: absolute;
        top: 3px;
        right: 3px;
        background-color: var(--color-forest);
        color: var(--color-white);
        font-size: 9px;
        font-weight: 700;
        min-width: 16px;
        height: 16px;
        line-height: 16px;
        border-radius: var(--radius-pill);
        text-align: center;
        padding: 0 3px;
    }

    /* 4. Mobile Slide Menu Drawer (Phase 3) */
    .ayur-mobile-drawer {
        width: 86vw !important;
        max-width: 360px !important;
        height: 100% !important;
        height: 100dvh !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        background-color: var(--color-ivory) !important;
        box-shadow: var(--shadow-drawer) !important;
        z-index: 2002 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .ayur-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid var(--color-border);
    }

    .ayur-drawer-close-btn {
        width: 44px;
        height: 44px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--color-forest);
        border-radius: var(--radius-btn);
    }

    .ayur-mobile-drawer-body {
        flex: 1;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding: 16px 20px 32px 20px;
    }

    .ayur-mobile-search-form {
        margin-bottom: 20px;
    }

    .ayur-mobile-search-input-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: var(--color-white);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-btn);
        padding: 0 14px;
        height: 46px;
    }

    .ayur-mobile-search-input-wrap input {
        border: none;
        background: transparent;
        width: 100%;
        font-size: 14px;
        outline: none;
        color: var(--color-charcoal);
    }

    .ayur-drawer-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--color-herbal);
        margin: 18px 0 8px 0;
    }

    .ayur-mobile-nav-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .ayur-mobile-nav-list li a {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 44px;
        padding: 6px 0;
        font-size: 14px;
        font-weight: 500;
        color: var(--color-charcoal);
        border-bottom: 1px solid var(--color-border-subtle);
        text-decoration: none;
    }

    .ayur-mobile-nav-list li a svg {
        color: var(--color-forest);
        flex-shrink: 0;
    }

    .ayur-mobile-support-box {
        margin: 10px 0 20px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .ayur-drawer-support-link {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 44px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-forest);
    }

    .ayur-drawer-legal-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        font-size: 12px;
        color: var(--color-muted);
        padding-top: 16px;
        border-top: 1px solid var(--color-border);
    }

    /* 5. Mobile Hero (Phase 4 & Phase 5) */
    .ayur-hero {
        padding-top: 24px;
        padding-bottom: 32px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .ayur-hero-grid {
        display: flex !important;
        flex-direction: column !important;
        gap: 20px !important;
        width: 100% !important;
        max-width: 100% !important;
        text-align: center;
        box-sizing: border-box;
    }

    .ayur-hero-text {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }

    .ayur-hero-eyebrow {
        font-size: 10px;
        letter-spacing: 0.14em;
        margin-bottom: 8px;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--color-herbal);
        display: inline-block;
    }

    .ayur-hero-title {
        font-size: 1.85rem;
        line-height: 1.2;
        margin-bottom: 10px;
        font-family: var(--font-display);
        color: var(--color-forest);
    }

    .ayur-hero-title span {
        color: var(--color-herbal);
        font-style: italic;
    }

    .ayur-hero-desc {
        font-size: 13px;
        line-height: 1.55;
        margin: 0 auto 18px auto;
        max-width: 320px;
        color: var(--color-muted);
    }

    .ayur-hero-actions {
        display: flex;
        flex-direction: row;
        gap: 10px;
        justify-content: center;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .ayur-hero-actions .ayur-btn-primary,
    .ayur-hero-actions .ayur-btn-secondary {
        flex: 1;
        height: 46px;
        padding: 0 12px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        white-space: nowrap;
        box-sizing: border-box;
    }

    /* Phase 4 & 5: Full-Width Authentic Product Visual & Feature Card */
    .ayur-hero-visual {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
    }

    .ayur-hero-stage {
        padding: 12px !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        background-color: var(--color-stage);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-card);
        box-shadow: none;
        box-sizing: border-box !important;
    }

    .ayur-hero-img-link {
        display: block;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 0 12px 0 !important;
    }

    .ayur-hero-img {
        width: 100% !important;
        max-width: 100% !important;
        aspect-ratio: 4 / 3 !important;
        object-fit: cover !important;
        border-radius: 8px;
        display: block;
    }

    .ayur-hero-card-meta {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        text-align: center;
        border-top: 1px solid var(--color-border);
        padding-top: 12px;
        box-sizing: border-box;
    }

    .ayur-hero-card-cat {
        display: block;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--color-herbal);
        margin-bottom: 4px;
    }

    .ayur-hero-card-title {
        font-family: var(--font-display);
        font-size: 15px;
        font-weight: 600;
        line-height: 1.3;
        color: var(--color-forest);
        margin-bottom: 6px;
        display: block;
    }

    .ayur-hero-card-title a {
        color: inherit;
        text-decoration: none;
    }

    .ayur-hero-card-pricing {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 6px;
        margin-bottom: 10px;
    }

    .ayur-hero-price {
        font-size: 16px;
        font-weight: 700;
        color: var(--color-forest);
    }

    .ayur-hero-mrp {
        font-size: 12px;
        color: var(--color-muted);
        text-decoration: line-through;
    }

    .ayur-hero-card-cta {
        width: 100%;
    }

    .ayur-hero-cta-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        height: 42px;
        background-color: var(--color-forest);
        color: var(--color-white);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        border-radius: var(--radius-btn);
        text-decoration: none;
        transition: background-color var(--transition-fast);
    }

    /* 6. Mobile Trust Strip (Phase 6: 2x2 Clean Layout) */
    .ayur-trust-rail {
        padding: 12px 0;
        background-color: var(--color-stage);
        width: 100% !important;
        max-width: 100% !important;
    }

    .ayur-trust-rail-inner {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        gap: 10px 12px !important;
        width: 100% !important;
    }

    .ayur-trust-rail-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .ayur-trust-rail-icon svg {
        width: 16px;
        height: 16px;
        color: var(--color-forest);
        flex-shrink: 0;
    }

    .ayur-trust-rail-text {
        font-size: 11px;
        font-weight: 600;
        color: var(--color-forest);
        white-space: nowrap;
    }

    .ayur-trust-rail-sep {
        display: none !important;
    }

    /* 7. Mobile Category Tiles (Phase 7: 2-Column with SVG icons) */
    .ayur-categories-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 10px !important;
        width: 100% !important;
    }

    .ayur-cat-block {
        padding: 14px 12px;
        border-radius: 10px;
        background-color: var(--color-white);
        border: 1px solid var(--color-border);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        width: 100%;
        box-sizing: border-box;
    }

    .ayur-cat-icon-wrap {
        margin-bottom: 8px;
        color: var(--color-forest);
        display: flex;
        align-items: center;
    }

    .ayur-cat-icon-wrap svg {
        width: 20px;
        height: 20px;
        color: var(--color-forest);
    }

    .ayur-cat-tag {
        font-size: 8px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--color-herbal);
        margin-bottom: 3px;
        display: block;
    }

    .ayur-cat-title {
        font-family: var(--font-display);
        font-size: 1.15rem;
        font-weight: 600;
        line-height: 1.25;
        color: var(--color-forest);
        margin-bottom: 8px;
    }

    .ayur-cat-link-text {
        font-size: 10px;
        font-weight: 600;
        color: var(--color-forest);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* 8. Mobile 2-Column Product Grid (Phase 8 & Phase 9) */
    .ayur-products-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 10px 12px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .ayur-product-card {
        padding: 10px;
        border-radius: 10px;
        background-color: var(--color-white);
        border: 1px solid var(--color-border);
        display: flex;
        flex-direction: column;
        position: relative;
        width: 100%;
        box-sizing: border-box;
    }

    .ayur-product-thumb-wrap {
        padding: 8px;
        margin-bottom: 8px;
        border-radius: 8px;
        background-color: var(--color-thumb-bg);
        width: 100%;
        aspect-ratio: 1 / 1;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-sizing: border-box;
    }

    .ayur-product-thumb-link {
        display: block;
        width: 100%;
        height: 100%;
    }

    .ayur-product-thumb {
        width: 100%;
        height: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 6px;
        display: block;
    }

    .ayur-product-cat {
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--color-herbal);
        display: block;
        margin-bottom: 2px;
    }

    .ayur-product-title {
        font-size: 13px;
        font-weight: 600;
        min-height: 35px;
        line-height: 1.35;
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--color-forest);
    }

    .ayur-product-title a {
        color: inherit;
        text-decoration: none;
    }

    .ayur-product-descriptor {
        font-size: 10px;
        color: var(--color-muted);
        margin-bottom: 6px;
    }

    .ayur-product-pricing {
        margin-bottom: 8px;
        gap: 4px;
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
    }

    .ayur-price {
        font-size: 14px;
        font-weight: 700;
        color: var(--color-forest);
    }

    .ayur-mrp {
        font-size: 11px;
        color: var(--color-muted);
        text-decoration: line-through;
    }

    .ayur-save-tag {
        font-size: 9px;
        font-weight: 700;
        background: rgba(183, 149, 82, 0.12);
        color: var(--color-gold);
        padding: 1px 5px;
        border-radius: 3px;
        width: 100%;
        margin-left: 0;
    }

    .ayur-card-btn {
        height: 36px;
        font-size: 11px;
        font-weight: 600;
        padding: 0 8px;
        border-radius: var(--radius-btn);
        border: 1px solid var(--color-border);
        color: var(--color-forest);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        width: 100%;
        text-decoration: none;
        transition: background var(--transition-fast);
        box-sizing: border-box;
    }

    .ayur-card-btn:hover {
        background-color: var(--color-forest);
        color: var(--color-white);
        border-color: var(--color-forest);
    }

    .ayur-card-badge-wrap {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 2;
    }

    .ayur-card-badge {
        font-size: 8px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: var(--radius-pill);
        background: var(--color-forest);
        color: var(--color-white);
        letter-spacing: 0.05em;
    }

    /* 9. Mobile Single Product Page Layout */
    .ayur-product-layout {
        display: flex;
        flex-direction: column;
        gap: 20px;
        margin-bottom: 32px;
        width: 100%;
    }

    .ayur-gallery-col {
        position: static;
        width: 100%;
    }

    .ayur-gallery-stage {
        padding: 16px;
        aspect-ratio: 1 / 1;
        border-radius: var(--radius-card);
        margin-bottom: 10px;
        width: 100%;
        box-sizing: border-box;
    }

    .ayur-gallery-thumbs {
        gap: 8px;
    }

    .ayur-gallery-thumb-btn {
        width: 52px;
        height: 52px;
        padding: 4px;
    }

    .ayur-single-title {
        font-size: 1.65rem;
        line-height: 1.25;
        margin-bottom: 8px;
    }

    .ayur-single-excerpt {
        font-size: 13px;
        line-height: 1.55;
        margin-bottom: 16px;
        padding-bottom: 14px;
    }

    .ayur-single-pricing-box {
        margin-bottom: 18px;
    }

    .ayur-single-price {
        font-size: 1.65rem;
    }

    .ayur-single-mrp {
        font-size: 1rem;
    }

    .ayur-single-savings {
        font-size: 11px;
        padding: 2px 8px;
    }

    .ayur-purchase-row {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 10px;
        margin-bottom: 20px;
        width: 100%;
    }

    .ayur-btn-buy-now {
        grid-column: 1 / -1;
        width: 100%;
        height: 48px;
    }

    .ayur-btn-add-cart {
        height: 48px;
        font-size: 12px;
        padding: 0 14px;
    }

    .ayur-qty-stepper {
        height: 48px;
    }

    .ayur-trust-2x2 {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        padding-top: 16px;
        width: 100%;
    }

    .ayur-trust-cell {
        padding: 8px 10px;
        gap: 6px;
    }

    .ayur-trust-cell .ayur-cell-icon svg {
        width: 15px;
        height: 15px;
    }

    .ayur-trust-cell .ayur-cell-text {
        font-size: 11px;
        white-space: nowrap;
    }

    .ayur-product-details-wrap {
        padding-top: 24px;
        margin-bottom: 32px;
        width: 100%;
    }

    .ayur-details-grid {
        grid-template-columns: 1fr;
        gap: 24px;
        width: 100%;
    }

    .ayur-detail-heading {
        font-size: 1.35rem;
    }

    .ayur-spec-card {
        padding: 16px;
        margin-bottom: 14px;
    }

    /* 10. Mobile Brand Story Section */
    .ayur-story-section {
        padding-top: 36px;
        padding-bottom: 36px;
        width: 100%;
    }

    .ayur-story-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
        width: 100%;
    }

    .ayur-story-title {
        font-size: 1.75rem;
    }

    .ayur-story-lead {
        font-size: 14px;
        line-height: 1.6;
    }

    .ayur-story-sub {
        font-size: 13px;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .ayur-story-pillars {
        grid-template-columns: 1fr;
        gap: 16px;
        padding-top: 16px;
        width: 100%;
    }

    /* 11. Mobile Footer */
    .ayur-footer-grid {
        grid-template-columns: 1fr;
        gap: 28px;
        width: 100%;
    }

    .ayur-footer-main {
        padding-top: 36px;
        padding-bottom: 88px; /* Space for bottom nav */
    }

    /* 12. Mobile Bottom Navigation (Phase 10) & Single Product Sticky Bar */
    .ayur-mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw !important;
        height: 58px;
        background-color: var(--color-white);
        border-top: 1px solid var(--color-border);
        display: flex;
        align-items: center;
        justify-content: space-around;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        z-index: 999;
        box-shadow: 0 -4px 16px rgba(23, 59, 47, 0.06);
    }

    .ayur-bottom-nav-item {
        flex: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        font-size: 10px;
        font-weight: 500;
        color: var(--color-muted);
        text-decoration: none;
        border: none;
        background: none;
        padding: 0;
    }

    .ayur-bottom-nav-item.active {
        color: var(--color-forest);
        font-weight: 700;
    }

    .ayur-bottom-cart-badge-wrap {
        position: relative;
        display: inline-flex;
    }

    .ayur-bottom-cart-badge-wrap .ayur-cart-badge {
        position: absolute;
        top: -4px;
        right: -8px;
        background-color: var(--color-forest);
        color: var(--color-white);
        font-size: 9px;
        font-weight: 700;
        min-width: 15px;
        height: 15px;
        line-height: 15px;
        text-align: center;
        border-radius: var(--radius-pill);
        padding: 0 3px;
    }

    /* Overlap Elimination: Single Product vs Standard Pages */
    body.single-product .ayur-mobile-bottom-nav {
        display: none !important;
    }

    body.single-product .ayur-mobile-sticky-bar {
        display: flex !important;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100vw !important;
        background-color: var(--color-white);
        border-top: 1px solid var(--color-border);
        padding: 10px 16px calc(10px + env(safe-area-inset-bottom, 0px)) 16px;
        z-index: 1000;
        box-shadow: 0 -4px 20px rgba(23, 59, 47, 0.08);
        box-sizing: border-box;
    }

    body.single-product .ayur-footer-main {
        padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px));
    }

    body:not(.single-product) .ayur-mobile-bottom-nav {
        display: flex;
        bottom: 0;
        z-index: 999;
    }

    body:not(.single-product) .ayur-mobile-sticky-bar {
        display: none !important;
    }

    /* 13. Mobile Cart Drawer (Phase 11) */
    .ayur-cart-drawer {
        width: min(92vw, 400px) !important;
        height: 100% !important;
        height: 100dvh !important;
    }
}

"""
    css = css[:m_start] + new_mobile_query + css[r_start:]
    print("Replaced @media (max-width: 768px) block")
else:
    print("Could not find media query markers!")

with open('themes/ayur-wellness/style.css', 'w', encoding='utf-8') as f:
    f.write(css)

print("Updated style.css successfully! Length:", len(css))
