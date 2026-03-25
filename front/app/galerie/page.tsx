import type { Metadata } from 'next';
import { ButtonComponent, GalleryComponent, HeroComponent, SectionDivider } from '#ui';
import type { GalleryItem } from '#/components/ui/Gallery.component';
import HeroPicture from '../../public/assets/gallery_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import { safeJsonLd } from '../jsonld';
import { getGalleryItems } from '#/lib/galleryApi';

export const metadata: Metadata = createPageMetadata({
  title: 'Nos réalisations | Caen',
  description:
    "Parcourez des exemples avant/après et des photos de prestations d'entretien de sépultures réalisées à Caen et alentours.",
  path: '/galerie',
  keywords: ['galerie', 'avant après', 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
});

export default async function Gallery() {
  let items: GalleryItem[] = [];
  let apiError: string | null = null;
  try {
    items = await getGalleryItems(60);
  } catch (e) {
    // Fallback silencieux : on garde la page fonctionnelle même si l'API est indisponible.
    console.error('GET /api/public/gallery-items failed:', e);
    apiError = e instanceof Error ? e.message : 'Impossible de charger la galerie depuis le back.';
    items = [];
  }

  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Avant / après nettoyage tombe | Caen',
    description:
      "Parcourez des exemples avant/après et des photos de prestations d'entretien de sépultures réalisées à Caen et alentours.",
    path: '/galerie',
    keywords: ['galerie', 'avant après', 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'Galerie', path: '/galerie' },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Galerie"
        subtitle="Parce que la réalité parle plus que mille mots"
        imageAlt="Galerie de réalisations avant et après entretien"
      />

      <section className="page-shell page-section">
        <GalleryComponent items={items} />
        {items.length === 0 ? (
          <>
            <div className="text-center text-xl text-[rgba(43,43,43,.75)]">
              Galerie en cours de construction, revenez bientôt.
            </div>
            {apiError && process.env.NODE_ENV === 'development' ? (
              <div className="mt-2 text-center text-xs text-red-600">{apiError}</div>
            ) : null}
          </>
        ) : null}
      </section>

      <SectionDivider flip />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center gap-8">
          <h3 className="section-heading text-3xl lg:text-4xl">Vos défunts ont droit à tout notre respect.</h3>
          <p className="leading-6 text-center">
            Confiez-nous l&apos;entretien de vos sépultures et profitez d&apos;un service professionnel et respectueux.
          </p>

          <ButtonComponent href="/contact" variant="gold" size="xl">
            Demander des renseignements
          </ButtonComponent>
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(breadcrumbJsonLd) }} />
    </>
  );
}
