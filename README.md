# Scouts Group Divi

A reusable Divi child theme for Australian Scout Groups. It is inspired by the interaction patterns of Scouts Australia—not a copy of its proprietary layouts or assets.

## Requirements

- WordPress 6.4 or newer
- Divi parent theme installed
- An approved Scouts group logo and any licensed brand assets supplied by the group

## Install

1. Upload `scouts-group-divi.zip` in **Appearance → Themes → Add New → Upload Theme**.
2. Activate **Scouts Group Divi**.
3. Open **Appearance → Customize → Scout Group Details** and set the group name, district/region, approved logo, and action URLs.
4. Create and assign a menu to **Scout Group Primary Menu**.

## Updates

The theme uses WordPress's native `Update URI` mechanism and checks the latest
published GitHub release. When a release tag is newer than the installed theme's
`Version`, WordPress shows it under **Dashboard → Updates** and allows a normal
one-click theme update.

Release requirements:

- Use a semantic tag such as `v1.2.1`.
- The tag's version must be higher than the `Version` in `style.css`.
- Attach a ZIP whose filename contains `scouts-divi-child`.
- The ZIP must contain one top-level folder named `scouts-divi-child`.
- Drafts and prereleases are intentionally ignored.

The checker caches successful GitHub responses for six hours and does not
require a GitHub access token for this public repository.

WordPress controls the **Enable auto-updates** preference; the theme does not
force it on or off. On multisite installations this preference is available to
Super Admins from **Network Admin → Themes**, not from an individual site's
Appearance screen.

### Multisite companion updater

Multisite Network Admin does not load an individual site's active theme
`functions.php` while checking network-wide theme updates. Install and
**Network Activate** the release asset
`scouts-divi-child-updater-vX.Y.Z.zip` once. The companion plugin registers
the same GitHub update provider in Network Admin, after which the
**Enable auto-updates** link is available for Scouts Group Divi.

After activating the plugin, visit **Network Admin → Dashboard → Updates** and
click **Check again** to rebuild WordPress's theme-update data.

### Automated publishing

Every push to `main` that changes theme files runs the release workflow. The
workflow reads the `Version` header from `style.css`; if the matching
`vX.Y.Z` release does not already exist, it:

1. validates the version,
2. creates a WordPress-ready `scouts-divi-child` folder,
3. builds and tests `scouts-divi-child-vX.Y.Z.zip`, and
4. builds the multisite updater companion plugin, and
5. creates the Git tag and GitHub release with generated notes.

Always increment `Version` in `style.css` for a new release. Keep
`SGD_VERSION` in `functions.php` identical so browser caches are refreshed.

## Reuse

No West Centenary values are hard-coded into templates other than safe Customizer defaults. Another group can replace every identity and action value without editing code.

## Brand and accessibility

The theme uses Nunito Sans when available and provides an accessible search drawer, mobile navigation, visible focus states, semantic landmarks, reduced-motion support and a skip link. Official logos, Gumtree graphics and fonts are intentionally not redistributed; obtain approved assets from the Scouts Australia Brand Centre and Scouts Queensland brand resources.

## Colour schemes

**Appearance → Customize → Colour Schemes** provides the national Scouts
Australia palette by default plus Branch presets for ACT, NSW, NT, Queensland,
South Australia, Tasmania, Victoria and Western Australia. Each preset controls
the header, overlay navigation, buttons, links and footer through CSS variables.

An approved logo can be uploaded for every preset. When a preset is selected,
its logo replaces the generic group logo automatically. Official logos and
other trademarked Branch artwork are deliberately not bundled or hotlinked;
site administrators should obtain current approved files from their Branch.

## Suggested menu

Home; About Us; Sections; Join; News & Events; Resources; Contact.

## License

Theme code is GPL-2.0-or-later. Scouts names, logos and brand assets remain the property of their respective owners and are not included.
