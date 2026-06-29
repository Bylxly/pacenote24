import { api } from './api.js';

(function () {
    const PROFILE = 'trekking';

    let map           = null;
    let waypoints     = [];
    let routeLayer    = null;
    let routeTimer    = null;
    let lastGeoJson   = null;   // letzter BRouter-Response für "Route speichern"

    document.getElementById('fullscreenBtn').addEventListener('click', function () {
        const el = document.getElementById('map');
        if (!el) return;
        (el.requestFullscreen || el.webkitRequestFullscreen || function(){}).call(el);
    });

    document.getElementById('clearBtn').addEventListener('click', function () {
        waypoints.forEach(wp => map.removeLayer(wp.marker));
        waypoints = [];
        lastGeoJson = null;
        clearRoute();
        showSaveBtn(false);
    });

    function initMap() {
        const el = document.getElementById('map');
        if (!el) return;
        map = L.map('map').setView([49.487, 8.466], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> | Routing: <a href="https://brouter.de">BRouter</a>'
        }).addTo(map);
        map.on('click', e => addWaypoint(e.latlng.lat, e.latlng.lng));
    }

    function addWaypoint(lat, lng) {
        const wp = { lat, lng, marker: null };
        waypoints.push(wp);

        const marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        wp.marker = marker;

        marker.on('drag', e => { wp.lat = e.latlng.lat; wp.lng = e.latlng.lng; });
        marker.on('dragend', () => { scheduleRoute(); });
        marker.on('click', e => {
            L.DomEvent.stopPropagation(e);
            removeWaypoint(waypoints.indexOf(wp));
        });

        scheduleRoute();
    }

    function removeWaypoint(i) {
        if (i < 0 || i >= waypoints.length) return;
        map.removeLayer(waypoints[i].marker);
        waypoints.splice(i, 1);
        scheduleRoute();
    }

    function showSaveBtn(show) {
        const btn = document.getElementById('saveBtn');
        if (btn) btn.style.display = show ? 'block' : 'none';
    }

    async function geocode(query) {
        const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&limit=1`;
        const resp = await fetch(url);
        if (!resp.ok) throw new Error('Geocoding-Fehler');
        const data = await resp.json();
        if (!data.length) throw new Error(`Ort nicht gefunden: "${query}"`);
        return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
    }

    async function calculateFromInputs() {
        const startVal = document.getElementById('startOrt')?.value.trim();
        const endVal   = document.getElementById('zielOrt')?.value.trim();
        const btn      = document.getElementById('routeBtn');
        if (!startVal || !endVal) return;

        btn.disabled = true;
        try {
            const [from, to] = await Promise.all([geocode(startVal), geocode(endVal)]);
            waypoints.forEach(wp => map.removeLayer(wp.marker));
            waypoints = [];
            addWaypoint(from.lat, from.lng);
            addWaypoint(to.lat, to.lng);
        } catch (err) {
            console.error(err);
        } finally {
            btn.disabled = false;
        }
    }

    async function computeRoute() {
        if (waypoints.length < 2) { clearRoute(); return; }
        const lonlats = waypoints.map(w => `${w.lng},${w.lat}`).join('|');
        const url = `https://brouter.de/brouter?lonlats=${encodeURIComponent(lonlats)}&profile=${PROFILE}&alternativeidx=0&format=geojson`;
        try {
            const resp = await fetch(url);
            if (!resp.ok) throw new Error('BRouter nicht erreichbar');
            const data = await resp.json();
            const feature = data?.features?.[0];
            if (!feature?.geometry?.coordinates) throw new Error('Keine Route gefunden');

            const coords = feature.geometry.coordinates.map(c => [c[1], c[0]]);
            if (routeLayer) map.removeLayer(routeLayer);
            routeLayer = L.polyline(coords, {
                color: '#3b82f6', weight: 5, opacity: 0.9, lineCap: 'round', lineJoin: 'round'
            }).addTo(map);
            map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });

            lastGeoJson = data;
            showSaveBtn(true);
        } catch (err) {
            console.error(err);
            showSaveBtn(false);
        }
    }

    function clearRoute() {
        if (routeLayer) { map.removeLayer(routeLayer); routeLayer = null; }
        lastGeoJson = null;
        showSaveBtn(false);
    }

    function scheduleRoute() {
        clearTimeout(routeTimer);
        if (waypoints.length < 2) { clearRoute(); return; }
        routeTimer = setTimeout(computeRoute, 400);
    }

    async function saveRoute() {
        if (!lastGeoJson) return;

        const title  = document.getElementById('routeTitle')?.value.trim() || null;
        const saveBtn = document.getElementById('saveConfirmBtn');
        const statusEl = document.getElementById('saveStatus');

        saveBtn.disabled = true;
        statusEl.textContent = 'Wird gespeichert...';
        statusEl.className = 'mt-2 small text-muted';

        const uid = parseInt(document.querySelector('meta[name="uid"]')?.content ?? '0', 10);
        if (!uid) { statusEl.textContent = 'Nicht angemeldet.'; statusEl.className = 'mt-2 small text-danger'; saveBtn.disabled = false; return; }

        const payload = {
            title,
            owner_user_id: uid,
            waypoints:    waypoints.map(w => ({ lat: w.lat, lng: w.lng })),
            distance_m:   parseInt(lastGeoJson.features?.[0]?.properties?.['track-length'] ?? 0, 10),
            json_data:    lastGeoJson,
        };

        try {
            const data = await api.post('/ajax/routes/create.php', payload);
            if (!data.success) throw new Error(data.error || 'Fehler');
            statusEl.textContent = 'Route gespeichert!';
            statusEl.className = 'mt-2 small text-success';
            setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('saveModal'))?.hide(), 1200);
        } catch (err) {
            statusEl.textContent = err.message;
            statusEl.className = 'mt-2 small text-danger';
        } finally {
            saveBtn.disabled = false;
        }
    }

    async function loadRouteById(routeId) {
        try {
            const result = await api.get('/ajax/routes.php', { id: routeId });
            if (!result.success) return;

            const route = result.data;
            const savedWaypoints = typeof route.waypoints === 'string'
                ? JSON.parse(route.waypoints)
                : (route.waypoints || []);
            const geoJson = typeof route.json_data === 'string'
                ? JSON.parse(route.json_data)
                : route.json_data;

            savedWaypoints.forEach(({ lat, lng }) => {
                const wp = { lat, lng, marker: null };
                waypoints.push(wp);
                const marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                wp.marker = marker;
                marker.on('drag', e => { wp.lat = e.latlng.lat; wp.lng = e.latlng.lng; });
                marker.on('dragend', () => scheduleRoute());
                marker.on('click', e => {
                    L.DomEvent.stopPropagation(e);
                    removeWaypoint(waypoints.indexOf(wp));
                });
            });

            const feature = geoJson?.features?.[0];
            if (feature?.geometry?.coordinates) {
                const coords = feature.geometry.coordinates.map(c => [c[1], c[0]]);
                if (routeLayer) map.removeLayer(routeLayer);
                routeLayer = L.polyline(coords, {
                    color: '#3b82f6', weight: 5, opacity: 0.9, lineCap: 'round', lineJoin: 'round'
                }).addTo(map);
                map.fitBounds(routeLayer.getBounds(), { padding: [50, 50] });
                lastGeoJson = geoJson;
                showSaveBtn(true);
            }
        } catch (err) {
            console.error(err);
        }
    }

    // Init
    document.addEventListener('DOMContentLoaded', function () {
        initMap();

        const routeId = new URLSearchParams(window.location.search).get('route_id');
        if (routeId) loadRouteById(parseInt(routeId, 10));

        document.getElementById('routeBtn')
            ?.addEventListener('click', calculateFromInputs);
        ['startOrt', 'zielOrt'].forEach(id =>
            document.getElementById(id)
                ?.addEventListener('keydown', e => { if (e.key === 'Enter') calculateFromInputs(); })
        );

        document.getElementById('saveConfirmBtn')
            ?.addEventListener('click', saveRoute);

        document.getElementById('saveModal')
            ?.addEventListener('show.bs.modal', () => {
                const el = document.getElementById('saveStatus');
                if (el) { el.textContent = ''; el.className = 'mt-2 small'; }
                const confirmBtn = document.getElementById('saveConfirmBtn');
                if (confirmBtn) confirmBtn.disabled = false;
            });
    });

})();
