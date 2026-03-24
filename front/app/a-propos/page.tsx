import type { Metadata } from 'next';
import { HeroComponent } from '#ui';
import HeroPicture from '../../public/assets/about_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';

export const metadata: Metadata = createPageMetadata({
  title: 'À propos',
  description:
    "Découvrez l'histoire et les valeurs de Lueur d'Éternité, service local d'entretien de sépultures à Caen.",
  path: '/a-propos',
});

export default function About() {
  const webPageJsonLd = buildWebPageJsonLd({
    title: 'À propos',
    description:
      "Découvrez l'histoire et les valeurs de Lueur d'Éternité, service local d'entretien de sépultures à Caen.",
    path: '/a-propos',
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'À propos', path: '/a-propos' },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="À propos de nous"
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
                Il faudrait que tu me donnes ton texte ici et que tu me donnes une belle photo de toi (photo claire et pro mais pas trop je pense).
              </p>
            </div>
          </div>
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbJsonLd) }} />
    </>
  );
}
