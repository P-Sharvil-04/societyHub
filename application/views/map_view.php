<!DOCTYPE html>
<html>

<head>
    <title>Google Maps Place Autocomplete</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        gmp-map {
            height: 100%;
        }

        gmp-basic-place-autocomplete {
            position: absolute;
            height: 30px;
            width: 500px;
            top: 10px;
            left: 10px;
            box-shadow: 4px 4px 5px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }
    </style>
</head>

<body>
    <gmp-map zoom="12" center="37.4220656,-122.0840897" map-id="b691102210af09218fe3ae44">
        <gmp-basic-place-autocomplete slot="control-inline-start-block-start"></gmp-basic-place-autocomplete>
    </gmp-map>

    <gmp-place-details-compact orientation="horizontal" style="width:400px; display:none; background:transparent;">
        <gmp-place-details-place-request></gmp-place-details-place-request>
        <gmp-place-standard-content></gmp-place-standard-content>
    </gmp-place-details-compact>

    <script>
        (g => {
            var h, a, k, p = "The Google Maps JavaScript API", c = "google", l = "importLibrary", q = "__ib__", m = document, b = window; b = b[c] || (b[c] = {}); var d = b.maps || (b.maps = {}), r = new Set, e = new URLSearchParams, u = () => h || (h = new Promise(async (f, n) => { a = m.createElement("script"); e.set("libraries", [...r] + ""); for (k in g) e.set(k.replace(/[A-Z]/g, t => "_" + t[0].toLowerCase()), g[k]); e.set("callback", c + ".maps." + q); a.src = `https://maps.${c}apis.com/maps/api/js?` + e; d[q] = f; a.onerror = () => h = n(Error(p + " could not load.")); a.nonce = m.querySelector("script[nonce]")?.nonce || ""; m.head.append(a) })); d[l] ? console.warn(p + " only loads once. Ignoring:", g) : d[l] = (f, ...n) => r.add(f) && u().then(() => d[l](f, ...n))
        })({ key: "YOUR API", v: "weekly", internalUsageAttributionIds: "gmp_git_jsapisamples_v1_places-api" });

        const placeAutocompleteElement = document.querySelector('gmp-basic-place-autocomplete');
        const placeDetailsElement = document.querySelector('gmp-place-details-compact');
        const placeDetailsParent = placeDetailsElement.parentElement;
        const gmpMapElement = document.querySelector('gmp-map');

        async function initMap() {
            await google.maps.importLibrary('places');
            const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');
            const { InfoWindow } = await google.maps.importLibrary('maps');

            const map = gmpMapElement.innerMap;

            const advancedMarker = new AdvancedMarkerElement({ map: map });
            const infoWindow = new InfoWindow({ minWidth: 360, disableAutoPan: true });

            placeAutocompleteElement.addEventListener('gmp-select', (event) => {
                if (!event.place || !event.place.id) return;

                placeDetailsParent.appendChild(placeDetailsElement);
                placeDetailsElement.style.display = 'block';

                const request = placeDetailsElement.querySelector('gmp-place-details-place-request');
                request.place = event.place.id;
            });

            placeDetailsElement.addEventListener('gmp-load', () => {
                const loc = placeDetailsElement.place.location;
                advancedMarker.position = loc;
                infoWindow.setContent(placeDetailsElement);
                infoWindow.open({ map, anchor: advancedMarker });
                map.setCenter(loc);
            });

            map.addListener('click', () => {
                infoWindow.close();
                advancedMarker.position = null;
            });
        }

        initMap();
    </script>
</body>
</html>
