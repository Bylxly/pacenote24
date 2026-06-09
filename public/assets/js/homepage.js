import { api } from './api.js';

let notes = [];
let currentIndex = 0;
let map = null;
let marker = null;
let routePolyline = null;
let activeCurveMarker = null;

//  DOM Elemente 
const fileInput      = document.getElementById('fileInput');
const noteCard       = document.getElementById('noteCard');
const noteName       = document.getElementById('noteName');
const noteDirection  = document.getElementById('noteDirection');
const distStart      = document.getElementById('distStart');
const distNext       = document.getElementById('distNext');
const noteRadius     = document.getElementById('noteRadius');
const noteSeverity   = document.getElementById('noteSeverity');
const noteProgress   = document.getElementById('noteProgress');
const mapPlaceholder = document.getElementById('mapPlaceholder');
const turnArrow      = document.getElementById('turnArrow');
const arrowPath      = document.getElementById('arrowPath');
const arrowHead      = document.getElementById('arrowHead');

//  Vollbild 
function vollbild() {
    const container = document.getElementById('kartenContainer') || document.getElementById('map');
    if (!container) return;
    if (container.requestFullscreen) {
        container.requestFullscreen();
    } else if (container.webkitRequestFullscreen) {
        container.webkitRequestFullscreen();
    }
}

//  Farbe nach Severity (1–6) 
function getColorBySeverity(severity) {
    const sev = Math.min(Math.max(parseInt(severity) || 2, 1), 6);
    switch (sev) {
        case 6: return '#00d2d3';
        case 5: return '#198754';
        case 4: return '#ffc107';
        case 3: return '#fd7e14';
        case 2: return '#dc3545';
        case 1: return '#9b0000';
        default: return '#3b82f6';
    }
}

//  BRouter:
async function fetchRouteSegment(from, to) {
    const url = `https://brouter.de/brouter?lonlats=${from.lng},${from.lat}|${to.lng},${to.lat}&profile=trekking&alternativeidx=0&format=geojson`;
    try {
        const response = await fetch(url);
        const data = await response.json();
        if (data?.features?.[0]?.geometry?.coordinates) {
            return data.features[0].geometry.coordinates.map(c => [c[1], c[0]]);
        }
    } catch (err) {
        console.warn(`BRouter Segment-Fehler (${from.lat},${from.lng} → ${to.lat},${to.lng}):`, err);
    }
    // Fallback: direkte Linie zwischen den zwei Punkten
    return [[from.lat, from.lng], [to.lat, to.lng]];
}

//  Karte initialisieren + Route Segment für Segment 
async function initMap(lat, lng) {
    if (!mapPlaceholder || !document.getElementById('map')) return;

    mapPlaceholder.style.display = 'none';
    map = L.map('map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap-Mitwirkende | Routing by BRouter'
    }).addTo(map);

    const validNotes = notes.filter(n => n.lat && n.lng);

    if (validNotes.length < 2) {
        // Nur ein Punkt – kein Routing nötig
        marker = L.marker([lat, lng]).addTo(map);
        updateMapAndCurveHighlight(notes[currentIndex]);
        return;
    }

    // Segmente nacheinander abrufen (nicht parallel → Rate-Limit vermeiden)
    let allCoords = [];
    for (let i = 0; i < validNotes.length - 1; i++) {
        const segment = await fetchRouteSegment(validNotes[i], validNotes[i + 1]);
        // Erstes Element jedes Folgesegments weglassen (Duplikat des letzten Punkts)
        if (allCoords.length > 0) {
            segment.shift();
        }
        allCoords = allCoords.concat(segment);
    }

    if (routePolyline) map.removeLayer(routePolyline);

    routePolyline = L.polyline(allCoords, {
        color: '#2563eb',
        weight: 6,
        opacity: 0.85,
        lineCap: 'round',
        lineJoin: 'round'
    }).addTo(map);

    map.fitBounds(routePolyline.getBounds(), { padding: [40, 40] });

    marker = L.marker([lat, lng]).addTo(map);
    updateMapAndCurveHighlight(notes[currentIndex]);
}

//  Karten-Marker aktualisieren 
function updateMapAndCurveHighlight(currentNote) {
    if (!map || !marker || !currentNote || !currentNote.lat || !currentNote.lng) return;

    marker.setLatLng([currentNote.lat, currentNote.lng]);

    if (activeCurveMarker) {
        map.removeLayer(activeCurveMarker);
    }

    const severityColor = getColorBySeverity(currentNote.severity);

    activeCurveMarker = L.circleMarker([currentNote.lat, currentNote.lng], {
        radius: 14,
        fillColor: severityColor,
        color: '#ffffff',
        weight: 3,
        fillOpacity: 0.95
    }).addTo(map);

    map.setView([currentNote.lat, currentNote.lng], 16);
}

//  Pfeil-Visualisierung 
function updateArrowVisual(direction, severity) {
    if (!turnArrow || !arrowPath || !arrowHead) return;

    const startX = 50;
    const startY = 85;
    const sev = Math.min(Math.max(parseInt(severity) || 2, 1), 6);

    let targetX = 50;
    let targetY = 15;
    let controlX = 50;
    let controlY = 50;

    if (direction === 'left') {
        targetX = 50 - (sev * 6.5);
        controlX = 50 + (sev * 5);
    } else if (direction === 'right') {
        targetX = 50 + (sev * 6.5);
        controlX = 50 - (sev * 5);
    }

    arrowPath.setAttribute('d', `M ${startX} ${startY} Q ${controlX} ${controlY} ${targetX} ${targetY}`);

    const angle = direction === 'left' ? -sev * 7 : (direction === 'right' ? sev * 7 : 0);
    arrowHead.setAttribute('points', `${targetX - 12},${targetY + 14} ${targetX},${targetY} ${targetX + 12},${targetY + 14}`);
    arrowHead.setAttribute('transform', `rotate(${angle} ${targetX} ${targetY})`);

    turnArrow.style.color = getColorBySeverity(sev);
}

//  Notes anzeigen 
function showNote() {
    if (notes.length === 0) return;
    const current = notes[currentIndex];

    const sev = Math.min(Math.max(parseInt(current.severity) || 2, 1), 6);
    const severityColor = getColorBySeverity(sev);

    if (noteName) {
        noteName.textContent = current.note || 'N/A';
        noteName.style.color = severityColor;
    }

    if (noteDirection) {
        noteDirection.textContent = current.direction || 'STRAIGHT';
        noteDirection.className = 'badge text-uppercase badge-direction';
        noteDirection.style.backgroundColor = severityColor;
        noteDirection.style.color = sev === 3 ? '#121214' : '#ffffff';
    }

    if (distStart) distStart.textContent = `${current.distance_from_start_m} m`;

    if (noteCard) {
        noteCard.style.setProperty('border-left-color', severityColor, 'important');
    }

    const distNextLabel = document.getElementById('distNextLabel');
    if (distNext && distNextLabel) {
        if (current.distance_to_next_note_m) {
            distNext.textContent = `${current.distance_to_next_note_m} m`;
            distNextLabel.textContent = 'Bis nächste Kurve';
        } else {
            distNext.textContent = `${current.distance_from_previous_note_m} m`;
            distNextLabel.textContent = 'Abstand zur letzten Kurve';
        }
    }

    if (noteRadius) noteRadius.textContent = current.radius_m ? `${current.radius_m} m` : 'N/A';

    if (noteSeverity) {
        noteSeverity.textContent = current.severity || '0';
        noteSeverity.style.color = severityColor;
    }

    if (noteProgress) noteProgress.textContent = `Klicken für nächste Note (${currentIndex + 1} / ${notes.length})`;

    updateArrowVisual(current.direction, current.severity);

    if (map) {
        updateMapAndCurveHighlight(current);
    }
}

//  File-Input 
if (fileInput) {
    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (e) {
            try {
                const data = JSON.parse(e.target.result);
                notes = data.notes || [];

                if (notes.length > 0) {
                    currentIndex = 0;

                    if (map) {
                        map.remove();
                        map = null;
                        routePolyline = null;
                        activeCurveMarker = null;
                        marker = null;
                    }

                    if (noteCard) noteCard.style.display = 'block';

                    showNote();

                    if (notes[0].lat && notes[0].lng) {
                        initMap(notes[0].lat, notes[0].lng).then(() => {
                            if (map) map.invalidateSize();
                        });
                    }
                } else {
                    alert('Keine Notizen ("notes") in der JSON-Struktur gefunden.');
                }
            } catch (error) {
                alert('Fehler beim Lesen der JSON-Datei.');
                console.error(error);
            }
        };
        reader.readAsText(file);
    });
}

//  Klick auf Karte → nächste Note 
if (noteCard) {
    noteCard.addEventListener('click', function () {
        if (notes.length === 0) return;
        currentIndex = (currentIndex + 1) % notes.length;
        showNote();
    });
}

//Funktion zu abrufen der json datei vom server

      document.addEventListener('DOMContentLoaded', () => {
          const btnFetchRoutes = document.getElementById('btnFetchRoutes');
          const serverRoutesSelect = document.getElementById('serverRoutesSelect');
          let serverTracks = [];

          // Importierte Route aus dem Routen-Import-Dialog (sessionStorage) anzeigen
          const imported = sessionStorage.getItem('importedRoute');
          if (imported) {
              sessionStorage.removeItem('importedRoute');
              try {
                  renderRouteOnMap(imported);
              } catch (e) {
                  alert('Importierte Route konnte nicht angezeigt werden.');
              }
          }

          // 1. Klick-Event für den API-Abruf
          btnFetchRoutes.addEventListener('click', async () => {
              btnFetchRoutes.disabled = true;
              btnFetchRoutes.textContent = 'Lade Routen...';

              try {
                  const result = await api.get('/ajax/routes.php');

                  if (result.success && Array.isArray(result.data)) {
                      serverTracks = result.data;
                      serverRoutesSelect.innerHTML = '<option selected disabled>Wähle eine Server-Route...</option>';
                      
                      serverTracks.forEach((track, index) => {
                          const option = document.createElement('option');
                          option.value = index;
                          option.textContent = track.title ? track.title : `Route #${track.route_id}`;
                          serverRoutesSelect.appendChild(option);
                      });

                      serverRoutesSelect.classList.remove('d-none'); 
                      btnFetchRoutes.textContent = 'Routenliste aktualisieren';
                  } else {
                      alert('Fehler: ' + (result.error || 'Ungültiges Format'));
                  }
              } catch (error) {
                  console.error('API-Fehler:', error);
                  alert('Fehler beim Laden vom Server.');
              } finally {
                  btnFetchRoutes.disabled = false;
              }
          });

          // 2. Event bei Auswahl einer Route aus dem Dropdown
          serverRoutesSelect.addEventListener('change', (e) => {
              const selectedIndex = e.target.value;
              const selectedTrack = serverTracks[selectedIndex];

              if (selectedTrack && selectedTrack.pacenotes_data) {
                  renderRouteOnMap(selectedTrack.pacenotes_data);
              } else {
                  alert('Für diese Route wurden noch keine Pacenotes generiert.');
              }
          });
      });

      // 3. Übergabe-Funktion
        function renderRouteOnMap(jsonData) {
            // json_data kann als String oder Objekt kommen
            const data = typeof jsonData === 'string' ? JSON.parse(jsonData) : jsonData;
            notes = data.notes || [];

            if (notes.length === 0) {
                alert('Diese Route enthält keine Pacenotes.');
                return;
            }

            currentIndex = 0;

            // alte Karte zurücksetzen
            if (map) {
                map.remove();
                map = null;
                routePolyline = null;
                activeCurveMarker = null;
                marker = null;
            }

            if (noteCard) noteCard.style.display = 'block';
            showNote();

            if (notes[0].lat && notes[0].lng) {
                initMap(notes[0].lat, notes[0].lng).then(() => {
                    if (map) map.invalidateSize();
                });
            }
        }