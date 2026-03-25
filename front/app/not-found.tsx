import type { Metadata } from 'next';
import Image from 'next/image';
import { ButtonComponent } from '#ui';

export const metadata: Metadata = {
  title: 'Page introuvable',
  description: "La page demandée n'existe pas.",
  robots: {
    index: false,
    follow: true,
  },
};

export default function NotFound() {
  return (
    <section className="page-shell page-section min-h-[68svh] flex items-center justify-center relative overflow-hidden">
      <div
        aria-hidden="true"
        className="pointer-events-none absolute inset-0 -z-10 flex items-start justify-center translate-y-10"
      >
        <div className="relative h-full w-full">
          <Image
            src="/assets/logo_line.webp"
            alt=""
            fill
            sizes="100vw"
            className="object-contain opacity-10"
            priority={false}
          />
        </div>
      </div>
      <div className="flex flex-col items-center justify-center text-center gap-6 py-10">
        <h1 className="text-3xl lg:text-4xl">404</h1>
        <p className="leading-6 max-w-[720px]">
          La page demandée n&apos;existe pas ou n&apos;est plus disponible. Vous pouvez revenir à l&apos;accueil
          pour continuer votre visite.
        </p>
        <ButtonComponent href="/" variant="gold" size="md">
          Retour à l&apos;accueil
        </ButtonComponent>
      </div>
    </section>
  );
}

