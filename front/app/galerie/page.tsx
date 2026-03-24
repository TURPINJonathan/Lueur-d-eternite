import type { Metadata } from 'next';
import { ButtonComponent, GalleryComponent, HeroComponent, SectionDivider } from '#ui';
import type { GalleryItem } from '#/components/ui/Gallery.component';
import HeroPicture from '../../public/assets/gallery_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';

export const metadata: Metadata = createPageMetadata({
  title: 'Galerie réalisations',
  description:
    "Parcourez des exemples avant/après et des photos de prestations d'entretien de sépultures réalisées à Caen et alentours.",
  path: '/galerie',
});

const galleryFixtures: GalleryItem[] = [
  {
    id: '1',
    kind: 'single',
    src: 'https://images.pexels.com/photos/10499270/pexels-photo-10499270.jpeg',
    thumb: 'https://images.pexels.com/photos/10499270/pexels-photo-10499270.jpeg?w=800',
    alt: 'Tombe propre et entretenue',
  },
  {
    id: '2',
    kind: 'compare',
    beforeSrc: 'https://images.pexels.com/photos/27434669/pexels-photo-27434669.jpeg',
    afterSrc: 'https://images.pexels.com/photos/11354344/pexels-photo-11354344.jpeg',
    thumb: 'https://images.pexels.com/photos/27434669/pexels-photo-27434669.jpeg?w=800',
    alt: 'Nettoyage de sépulture avant après',
  },
  {
    id: '3',
    kind: 'single',
    src: 'https://images.pexels.com/photos/29365618/pexels-photo-29365618.jpeg',
    thumb: 'https://images.pexels.com/photos/29365618/pexels-photo-29365618.jpeg?w=800',
    alt: 'Sépulture fleurie',
  },
  {
    id: '4',
    kind: 'single',
    src: 'https://images.pexels.com/photos/34351087/pexels-photo-34351087.jpeg',
    thumb: 'https://images.pexels.com/photos/34351087/pexels-photo-34351087.jpeg?w=800',
    alt: 'Cimetière calme et entretenu',
  },
  {
    id: '5',
    kind: 'single',
    src: 'https://images.pexels.com/photos/6494467/pexels-photo-6494467.jpeg',
    thumb: 'https://images.pexels.com/photos/6494467/pexels-photo-6494467.jpeg?w=800',
    alt: 'Détail pierre tombale propre',
  },
  {
    id: '6',
    kind: 'single',
    src: 'https://images.pexels.com/photos/6841492/pexels-photo-6841492.jpeg',
    thumb: 'https://images.pexels.com/photos/6841492/pexels-photo-6841492.jpeg?w=800',
    alt: 'Ambiance paisible et naturelle',
  },
];

export default function Gallery() {
  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Galerie réalisations',
    description:
      "Parcourez des exemples avant/après et des photos de prestations d'entretien de sépultures réalisées à Caen et alentours.",
    path: '/galerie',
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
        <GalleryComponent items={galleryFixtures} />
      </section>

      <SectionDivider flip />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center gap-8">
          <h3 className="section-heading text-3xl lg:text-4xl">
            Vos défunts ont droit à tout notre respect.
          </h3>
          <p className="leading-6 text-center">
            Confiez-nous l&apos;entretien de vos sépultures et profitez d&apos;un service professionnel et respectueux.
          </p>

          <ButtonComponent href="/contact" variant="gold" size="xl">
            Demander des renseignements
          </ButtonComponent>
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbJsonLd) }} />
    </>
  );
}
