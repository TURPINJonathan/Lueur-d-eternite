'use client';

import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import Image from 'next/image';
import { useEffect, useState } from 'react';
import { ReactCompareSlider, ReactCompareSliderImage } from 'react-compare-slider';

import LogoLoader from './LogoLoader.component';

export type GalleryItem =
  | {
      id: string;
      kind: 'single';
      src: string;
      thumb: string;
      alt: string;
    }
  | {
      id: string;
      kind: 'compare';
      beforeSrc: string;
      afterSrc: string;
      thumb: string;
      afterThumb?: string;
      alt: string;
    };

interface GalleryComponentProps {
  items: GalleryItem[];
}

function GalleryLightboxMedia({ item }: { item: GalleryItem }) {
  const [loadsPending, setLoadsPending] = useState(() => (item.kind === 'single' ? 1 : 2));

  const onPieceLoaded = () => setLoadsPending((n) => Math.max(0, n - 1));
  const busy = loadsPending > 0;

  return (
    <div className="relative min-h-[200px]">
      {item.kind === 'single' ? (
        <Image
          src={item.src}
          alt={item.alt}
          width={1600}
          height={1000}
          sizes="100vw"
          className="max-h-[85vh] w-full object-contain"
          onLoadingComplete={onPieceLoaded}
          onError={onPieceLoaded}
        />
      ) : (
        <ReactCompareSlider
          itemOne={
            <ReactCompareSliderImage
              src={item.beforeSrc}
              alt={`${item.alt} - avant`}
              onLoad={onPieceLoaded}
              onError={onPieceLoaded}
            />
          }
          itemTwo={
            <ReactCompareSliderImage
              src={item.afterSrc}
              alt={`${item.alt} - après`}
              onLoad={onPieceLoaded}
              onError={onPieceLoaded}
            />
          }
        />
      )}

      {busy ? (
        <div className="absolute inset-0 z-[95] flex min-h-[min(85vh,560px)] items-center justify-center bg-black/55 backdrop-blur-[2px]">
          <LogoLoader size="md" label="Chargement de l'image" />
        </div>
      ) : null}
    </div>
  );
}

export default function GalleryComponent({ items }: GalleryComponentProps) {
  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const activeItem = activeIndex !== null ? items[activeIndex] : null;

  useEffect(() => {
    if (!activeItem) return;

    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setActiveIndex(null);
    };

    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [activeItem]);

  const showPrevious = () => {
    if (activeIndex === null || activeIndex <= 0) return;
    setActiveIndex(activeIndex - 1);
  };

  const showNext = () => {
    if (activeIndex === null || activeIndex >= items.length - 1) return;
    setActiveIndex(activeIndex + 1);
  };

  return (
    <>
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        {items.map((item, index) => (
          <div key={item.id} className="overflow-hidden rounded-[var(--radius-md)] shadow-xl">
            {item.kind === 'single' ? (
              <button
                type="button"
                onClick={() => setActiveIndex(index)}
                className="block h-64 w-full cursor-pointer overflow-hidden text-left"
                aria-label={`Agrandir ${item.alt}`}
              >
                <Image
                  src={item.thumb}
                  alt={item.alt}
                  width={800}
                  height={500}
                  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                  className="h-64 w-full object-cover transition-transform duration-300 hover:scale-[1.03]"
                  loading="lazy"
                  fetchPriority="low"
                />
              </button>
            ) : (
              <button
                type="button"
                onClick={() => setActiveIndex(index)}
                className="relative block h-64 w-full cursor-pointer overflow-hidden text-left"
                aria-label={`Ouvrir la comparaison ${item.alt}`}
              >
                <Image
                  src={item.afterThumb ?? item.afterSrc}
                  alt={`${item.alt} - aperçu après`}
                  fill
                  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                  className="object-cover"
                  loading="lazy"
                  fetchPriority="low"
                />
                <div className="absolute inset-y-0 left-0 w-1/2 overflow-hidden">
                  <Image
                    src={item.thumb}
                    alt={`${item.alt} - aperçu avant`}
                    fill
                    sizes="(max-width: 640px) 50vw, (max-width: 1024px) 25vw, 16vw"
                    className="h-full w-full object-cover object-left"
                    loading="lazy"
                    fetchPriority="low"
                  />
                </div>
                <div className="pointer-events-none absolute inset-y-0 left-1/2 w-[2px] -translate-x-1/2 bg-white/90 shadow-[0_0_10px_rgba(0,0,0,0.35)]" />
              </button>
            )}
          </div>
        ))}
      </div>

      {activeItem && activeIndex !== null && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
          onClick={() => setActiveIndex(null)}
          role="dialog"
          aria-modal="true"
          aria-label={`Visualisation de l'image ${activeIndex + 1}`}
        >
          <div
            className="relative w-full max-w-6xl overflow-hidden rounded-[var(--radius-lg)] bg-black shadow-2xl"
            onClick={(event) => event.stopPropagation()}
          >
            <button
              type="button"
              onClick={() => setActiveIndex(null)}
              className="absolute right-3 top-3 z-[100] rounded-full h-11 w-11 cursor-pointer bg-black/60 px-3 py-1 text-sm text-white"
              aria-label="Fermer la galerie"
            >
              <X />
            </button>

            <GalleryLightboxMedia key={`${activeIndex}-${activeItem.id}`} item={activeItem} />

            <button
              type="button"
              onClick={showPrevious}
              disabled={activeIndex <= 0}
              className="absolute z-[99] cursor-pointer left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white disabled:cursor-not-allowed disabled:opacity-40"
              aria-label="Image précédente"
            >
              <ChevronLeft className="h-6 w-6" />
            </button>
            <button
              type="button"
              onClick={showNext}
              disabled={activeIndex >= items.length - 1}
              className="absolute z-[99] cursor-pointer right-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white disabled:cursor-not-allowed disabled:opacity-40"
              aria-label="Image suivante"
            >
              <ChevronRight className="h-6 w-6" />
            </button>
          </div>
        </div>
      )}
    </>
  );
}
