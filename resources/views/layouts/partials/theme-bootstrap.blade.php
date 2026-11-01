{{--
    Early theme bootstrap: applies the stored bec-theme preference (set by the
    theme toggle) or the visitor's prefers-color-scheme before first paint.
    Shared by the admin layout and the student survey pages so the whole app
    follows one theme. Must stay in <head>, before the Vite assets.
--}}
<script>
    (function () {
        var stored = null;

        try {
            stored = localStorage.getItem('bec-theme');
        } catch (error) {
            stored = null;
        }

        var media = window.matchMedia('(prefers-color-scheme: dark)');
        var meta = document.querySelector('meta[name="theme-color"]');
        var lightChrome = meta ? meta.getAttribute('content') : '#eef4ff';

        function applyTheme(dark) {
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.style.colorScheme = dark ? 'dark' : 'light';

            if (meta) {
                meta.setAttribute('content', dark ? '#070d1d' : lightChrome);
            }

            window.dispatchEvent(new Event('resize'));
        }

        window.becTheme = {
            set: function (dark, persist) {
                applyTheme(dark);

                if (persist) {
                    stored = dark ? 'dark' : 'light';

                    try {
                        localStorage.setItem('bec-theme', stored);
                    } catch (error) {
                        /* storage unavailable */
                    }
                }
            },
        };

        applyTheme(stored ? stored === 'dark' : media.matches);

        media.addEventListener('change', function (event) {
            if (!stored) {
                applyTheme(event.matches);
            }
        });

        window.addEventListener('storage', function (event) {
            if (event.key === 'bec-theme') {
                stored = event.newValue;
                applyTheme(stored ? stored === 'dark' : media.matches);
            }
        });

        // Chart.js colors are hardcoded inside the bundle; remap them at draw time
        // so canvas charts match the active theme without touching app logic.
        if (window.CanvasRenderingContext2D) {
            var chartColors = {
                '#0b1739': '#e9efff',
                '#71809d': '#96a7c9',
                '#edf1f7': 'rgba(150, 173, 218, 0.16)',
            };

            var proto = CanvasRenderingContext2D.prototype;

            ['fillStyle', 'strokeStyle'].forEach(function (property) {
                var descriptor = Object.getOwnPropertyDescriptor(proto, property);

                Object.defineProperty(proto, property, {
                    get: function () {
                        return descriptor.get.call(this);
                    },
                    set: function (value) {
                        if (
                            typeof value === 'string'
                            && document.documentElement.classList.contains('dark')
                            && chartColors[value.toLowerCase()]
                        ) {
                            value = chartColors[value.toLowerCase()];
                        }

                        descriptor.set.call(this, value);
                    },
                });
            });
        }
    })();
</script>
