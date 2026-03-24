import { ReactNode } from 'react';
import Image from 'next/image';
import type { StaticImageData } from 'next/image';

interface HeroComponentProps {
  picture: StaticImageData | { src: string };
  title: string;
  subtitle: string;
  ctaPrimary?: ReactNode;
  ctaSecondary?: ReactNode;
  priority?: boolean;
  imageAlt?: string;
}

export default function HeroComponent({
  picture,
  title,
  subtitle,
  ctaPrimary,
  ctaSecondary,
  priority = false,
  imageAlt,
}: HeroComponentProps) {
  return (
    <section className="relative min-h-[75svh] -mt-[var(--nav-height)] mb-10 shadow-2xl rounded-bl-[var(--radius-lg)] rounded-br-[var(--radius-lg)]">
      <Image
        src={picture.src}
        alt={imageAlt ?? title}
        fill
        sizes="100vw"
        priority={priority}
        className="object-cover object-center rounded-bl-[var(--radius-lg)] rounded-br-[var(--radius-lg)]"
      />
      <div className="relative w-full min-h-[75svh] bg-[var(--bg-overlay)] rounded-bl-[var(--radius-lg)] rounded-br-[var(--radius-lg)] flex flex-col gap-5 justify-center items-center text-[var(--color-white)]">
        <h1 className="text-center max-w-[70%] text-3xl lg:text-4xl">{title}</h1>

        <p className="text-center max-w-[75%] font-body !font-light text-xl">{subtitle}</p>

        {(ctaPrimary || ctaSecondary) && (
          <div className="absolute w-1/2 bottom-0 translate-y-1/2 flex flex-wrap gap-2 lg:gap-4 justify-center items-center">
            {ctaPrimary && <div className="flex-1 basis-[250px]">{ctaPrimary}</div>}
            {ctaSecondary && <div className="hidden lg:flex flex-1 basis-[250px]">{ctaSecondary}</div>}
          </div>
        )}
      </div>
    </section>
  );
}
