'use client';

import { ChevronLeft, ChevronRight, X } from 'lucide-react';
import Image from 'next/image';
import { useEffect, useState } from 'react';
import { ReactCompareSlider, ReactCompareSliderImage } from 'react-compare-slider';

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
      alt: string;
    };

interface GalleryComponentProps {
  items: GalleryItem[];
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
                  src={item.afterSrc}
                  alt={`${item.alt} - après`}
                  fill
                  sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                  className="object-cover"
                />
                <div className="absolute inset-y-0 left-0 w-1/2 overflow-hidden">
                  <Image
                    src={item.beforeSrc}
                    alt={`${item.alt} - avant`}
                    fill
                    sizes="(max-width: 640px) 50vw, (max-width: 1024px) 25vw, 16vw"
                    className="h-full w-full object-cover object-left"
                  />
                </div>
                <div className="pointer-events-none absolute inset-y-0 left-1/2 w-[2px] -translate-x-1/2 bg-white/90 shadow-[0_0_10px_rgba(0,0,0,0.35)]" />
              </button>
            )}
          </div>
        ))}
      </div>

      {activeItem && (
        <div
          className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
          onClick={() => setActiveIndex(null)}
          role="dialog"
          aria-modal="true"
          aria-label={`Visualisation de l'image ${activeIndex !== null ? activeIndex + 1 : ''}`}
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

            {activeItem.kind === 'single' ? (
              <Image
                src={activeItem.src}
                alt={activeItem.alt}
                width={1600}
                height={1000}
                sizes="100vw"
                className="max-h-[85vh] w-full object-contain"
              />
            ) : (
              <>
                <ReactCompareSlider
                  itemOne={<ReactCompareSliderImage src={activeItem.beforeSrc} alt={`${activeItem.alt} - avant`} />}
                  itemTwo={<ReactCompareSliderImage src={activeItem.afterSrc} alt={`${activeItem.alt} - après`} />}
                />
              </>
            )}

            <button
              type="button"
              onClick={showPrevious}
              disabled={activeIndex === null || activeIndex <= 0}
              className="absolute z-[99] cursor-pointer left-3 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/60 text-white disabled:cursor-not-allowed disabled:opacity-40"
              aria-label="Image précédente"
            >
              <ChevronLeft className="h-6 w-6" />
            </button>
            <button
              type="button"
              onClick={showNext}
              disabled={activeIndex === null || activeIndex >= items.length - 1}
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
