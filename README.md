# Omeka-S PueueJobDispatcher module

This module uses [Pueue](https://github.com/Nukesor/pueue) to run background jobs in parallel.
Pueue allows to limit the number of simultaneous background jobs, relaxing the server.

## Requirements

- Omeka-S 3.X
- [Pueue](https://github.com/Nukesor/pueue) client and daemon installed
- A running instance of the Pueued daemon under the same user as the web server / PHP cli. For Apache2 this is usually www-data.

## License

This module is published under the [MIT](LICENSE) license.

## Copyright

* Copyright [Ghent Centre for Digital Humanities](https://www.ghentcdh.ugent.be), 2024

## Credits

Development by [Ghent Centre for Digital Humanities - Ghent University](https://www.ghentcdh.ugent.be/). Funded by the [GhentCDH research projects](https://www.ghentcdh.ugent.be/projects).

<img src="https://www.ghentcdh.ugent.be/ghentcdh_logo_blue_text_transparent_bg_landscape.svg" alt="Landscape" width="500">
