import type { Metadata } from 'next';
import { HeroComponent } from '#ui';
import HeroPicture from '../../public/assets/about_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import { safeJsonLd } from '../jsonld';
import { Allura } from 'next/font/google';

export const metadata: Metadata = createPageMetadata({
  title: 'À propos | Nettoyage tombe Caen (Calvados)',
  description:
    "Découvrez l'histoire et les valeurs de Lueur d'Éternité, service local de nettoyage et soin de sépultures (tombes) à Caen (Calvados).",
  path: '/a-propos',
  keywords: ['à propos', "Lueur d'Éternité", 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
});

export const allura = Allura({
  weight: '400',
  subsets: ['latin'],
});

export default function About() {
  const webPageJsonLd = buildWebPageJsonLd({
    title: 'À propos | Nettoyage tombe Caen (Calvados)',
    description:
      "Découvrez l'histoire et les valeurs de Lueur d'Éternité, service local de nettoyage et soin de sépultures (tombes) à Caen (Calvados).",
    path: '/a-propos',
    keywords: ['à propos', "Lueur d'Éternité", 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'À propos', path: '/a-propos' },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="L'histoire de Lueur d'&Eacute;ternité"
        subtitle="Ne confiez pas les sépultures de vos défunts à n'importe qui."
        imageAlt="Présentation de l'équipe Lueur d'Éternité"
      />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center justify-center gap-10 lg:gap-8">
          <div className="w-full flex flex-col lg:flex-row gap-5 lg:gap-10">
            <div
              className="services-image-frame !p-0 bg-no-repeat bg-cover bg-center flex-1 hidden lg:block"
              style={{ backgroundImage: `url(${HeroPicture.src})` }}
              role="img"
              aria-label="Portrait de la fondatrice de Lueur d'Éternité"
            />

            <div className="flex-1 flex flex-col justify-center gap-3 pb-4 lg:py-4">
              <h3 className="px-5 text-3xl lg:text-4xl">La lettre d&apos;Émilie</h3>
              <p className="italic leading-6 indent-8 px-5">
                Il y a des lieux qui méritent plus que du silence. Des lieux qui racontent une histoire, un amour, une
                vie.
              </p>
              <p className="italic leading-6 px-5">
                Prendre soin d’une sépulture, ce n’est pas simplement nettoyer une pierre. C’est honorer la mémoire de
                ceux qui nous ont quittés, préserver un lien malgré le temps.
              </p>
              <p className="italic leading-6 px-5">
                C’est avec respect, douceur et discrétion que j’interviens pour entretenir ces lieux si précieux.
              </p>
              <p className="italic leading-6 px-5">
                Chaque geste est réalisé avec attention, comme si je le faisais pour mes propres proches. Je comprends
                que la distance, le manque de temps ou les difficultés du quotidien peuvent empêcher de venir aussi
                souvent qu’on le souhaiterait. C’est pourquoi je vous propose un service humain, sincère et de
                confiance.
              </p>
              <p className="italic leading-6 px-5">
                Mon engagement est simple :<br />
                <b>Redonner éclat, dignité et sérénité</b> aux sépultures qui me sont confiées.
              </p>
              <p className="italic leading-6 px-5">
                Parce qu’un lieu propre et entretenu, c&apos;est une belle façon de continuer à dire :
                <b className={`block text-end font-semibold text-3xl pr-45 mt-4 w-full ${allura.className}`}>
                  Je pense à toi
                </b>
              </p>
              <p className={`italic text-end text-4xl mt-6 pr-40 ${allura.className}`}>Émilie</p>
            </div>
          </div>
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(breadcrumbJsonLd) }} />
    </>
  );
}
