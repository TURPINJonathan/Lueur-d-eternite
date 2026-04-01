'use client';

import { Circle, MapContainer, Polygon, TileLayer, useMap } from 'react-leaflet';
import type { LatLngBoundsExpression, LatLngExpression, LatLngTuple } from 'leaflet';
import { useEffect } from 'react';

type TradeAreaMode = 'circle' | 'polygon';

interface TradeAreaMapClientProps {
  mode: TradeAreaMode;
  center: LatLngTuple;
  radiusKm?: number;
  polygon?: LatLngTuple[];
  className?: string;
  zoomBoost?: number;
}

function FitToZone({
  mode,
  center,
  radiusKm,
  polygon,
  zoomBoost = 1,
}: Pick<TradeAreaMapClientProps, 'mode' | 'center' | 'radiusKm' | 'polygon' | 'zoomBoost'>) {
  const map = useMap();

  useEffect(() => {
    if (mode === 'polygon' && polygon && polygon.length > 2) {
      map.fitBounds(polygon as LatLngBoundsExpression, {
        padding: [10, 10],
      });

      if (zoomBoost > 0) {
        map.setZoom(map.getZoom() + zoomBoost);
      }

      return;
    }

    if (mode === 'circle' && radiusKm) {
      const latOffset = radiusKm / 111;
      const lngOffset = radiusKm / (111 * Math.cos((center[0] * Math.PI) / 180));

      const bounds: LatLngBoundsExpression = [
        [center[0] - latOffset, center[1] - lngOffset],
        [center[0] + latOffset, center[1] + lngOffset],
      ];

      map.fitBounds(bounds, { padding: [10, 10] });

      if (zoomBoost > 0) {
        map.setZoom(map.getZoom() + zoomBoost);
      }
    }
  }, [map, mode, center, radiusKm, polygon, zoomBoost]);

  return null;
}

export default function MapComponent({
  mode,
  center,
  radiusKm = 15,
  polygon,
  className,
  zoomBoost = 1,
}: TradeAreaMapClientProps) {
  const zonePathOptions = {
    color: 'rgba(159, 123, 34, 0.72)',
    weight: 1.6,
    fillColor: '#d4af37',
    fillOpacity: 0.12,
  };

  return (
    <div
      className={[
        'relative isolate z-0 overflow-hidden rounded-[1.5rem]',
        'bg-[linear-gradient(180deg,rgba(255,255,255,0.88),rgba(244,237,228,0.78))]',
        'shadow-[0_20px_40px_rgba(0,0,0,0.10),inset_0_1px_0_rgba(255,255,255,0.7)]',
        className ?? '',
      ].join(' ')}
    >
      <div className="pointer-events-none absolute inset-x-0 top-0 z-[500] h-20 bg-[linear-gradient(180deg,rgba(255,248,230,0.45),rgba(255,248,230,0))]" />

      <MapContainer
        center={center as LatLngExpression}
        zoom={9}
        scrollWheelZoom={false}
        zoomControl={true}
        attributionControl={true}
        dragging={true}
        doubleClickZoom={false}
        className="trade-area-map h-[360px] w-full"
      >
        <TileLayer
          attribution="&copy; OpenStreetMap contributors"
          url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
        />

        <FitToZone mode={mode} center={center} radiusKm={radiusKm} polygon={polygon} zoomBoost={zoomBoost} />

        {mode === 'circle' ? (
          <Circle center={center} radius={radiusKm * 1000} pathOptions={zonePathOptions} />
        ) : polygon && polygon.length > 2 ? (
          <Polygon positions={polygon} pathOptions={zonePathOptions} />
        ) : null}
      </MapContainer>
    </div>
  );
}
