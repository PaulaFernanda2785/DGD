(function (global) {
    function parseTransform(value) {
        if (!value || value === 'none') {
            return { x: 0, y: 0 };
        }

        var matrix3d = value.match(/matrix3d\(([^)]+)\)/);
        if (matrix3d) {
            var values3d = matrix3d[1].split(',').map(parseFloat);
            return { x: values3d[12] || 0, y: values3d[13] || 0 };
        }

        var matrix = value.match(/matrix\(([^)]+)\)/);
        if (matrix) {
            var values = matrix[1].split(',').map(parseFloat);
            return { x: values[4] || 0, y: values[5] || 0 };
        }

        var translate = value.match(/translate(?:3d)?\((-?[0-9.]+)px,\s*(-?[0-9.]+)px/i);
        if (translate) {
            return { x: parseFloat(translate[1]) || 0, y: parseFloat(translate[2]) || 0 };
        }

        return { x: 0, y: 0 };
    }

    function elementOffset(element, root) {
        var elementRect = element.getBoundingClientRect();
        var rootRect = root.getBoundingClientRect();

        return {
            x: elementRect.left - rootRect.left,
            y: elementRect.top - rootRect.top
        };
    }

    function drawTiles(context, mapContainer) {
        var tiles = mapContainer.querySelectorAll('.leaflet-tile-pane img.leaflet-tile-loaded, .leaflet-tile-pane img.leaflet-tile');

        Array.prototype.forEach.call(tiles, function (tile) {
            if (!tile.complete || tile.naturalWidth === 0) {
                return;
            }

            var offset = elementOffset(tile, mapContainer);
            var width = tile.offsetWidth || tile.naturalWidth || 256;
            var height = tile.offsetHeight || tile.naturalHeight || 256;

            try {
                context.drawImage(tile, offset.x, offset.y, width, height);
            } catch (error) {
                // Ignora tiles que o navegador bloqueou por CORS; as camadas operacionais ainda serao capturadas.
            }
        });
    }

    function drawLayerCanvases(context, mapContainer) {
        var canvases = mapContainer.querySelectorAll('.leaflet-overlay-pane canvas');

        Array.prototype.forEach.call(canvases, function (sourceCanvas) {
            if (!sourceCanvas.width || !sourceCanvas.height) {
                return;
            }

            var offset = elementOffset(sourceCanvas, mapContainer);
            try {
                context.drawImage(sourceCanvas, offset.x, offset.y);
            } catch (error) {
                // Se uma camada especifica falhar, mantem a captura das demais.
            }
        });
    }

    function drawSvgOverlays(context, mapContainer) {
        var svgs = mapContainer.querySelectorAll('.leaflet-overlay-pane svg');

        Array.prototype.forEach.call(svgs, function (svg) {
            var xml = new XMLSerializer().serializeToString(svg);
            var image = new Image();
            var offset = elementOffset(svg, mapContainer);
            var width = svg.clientWidth || svg.getAttribute('width') || 0;
            var height = svg.clientHeight || svg.getAttribute('height') || 0;

            if (!width || !height) {
                return;
            }

            image.onload = function () {
                try {
                    context.drawImage(image, offset.x, offset.y, Number(width), Number(height));
                } catch (error) {
                    // SVG nao e essencial para mapas que usam renderer canvas.
                }
            };
            image.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
        });
    }

    global.leafletImage = function (map, callback) {
        try {
            var size = map.getSize();
            var container = map.getContainer();
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');

            canvas.width = Math.max(1, Math.round(size.x));
            canvas.height = Math.max(1, Math.round(size.y));

            context.fillStyle = '#eef4f7';
            context.fillRect(0, 0, canvas.width, canvas.height);

            drawTiles(context, container);
            drawLayerCanvases(context, container);
            drawSvgOverlays(context, container);

            callback(null, canvas);
        } catch (error) {
            callback(error);
        }
    };
})(window);
