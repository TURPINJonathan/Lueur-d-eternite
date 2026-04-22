import type { Metadata } from 'next';
import { CardComponent, HeroComponent, SectionDivider } from '#ui';
import { Star } from 'lucide-react';
import HeroPicture from '../../public/assets/review_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import { safeJsonLd } from '../jsonld';
import { getReviews } from '#lib';
import { seoConfig } from '../seo';
import ReviewForm from './ReviewForm';

export const metadata: Metadata = createPageMetadata({
  title: 'Avis clients | Entretien de sépultures à Caen',
  description:
    "Consultez les avis clients sur nos prestations d'entretien et de nettoyage de sépultures à Caen et ses alentours.",
  path: '/avis',
  keywords: ['avis', 'témoignages', 'sépulture', 'tombe', 'entretien', 'nettoyage', 'Caen'],
});

export default async function ReviewsPage() {
  const reviews = await getReviews(120);
  const averageRating = reviews.length > 0 ? reviews.reduce((sum, review) => sum + review.rate, 0) / reviews.length : 0;

  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Avis clients | Entretien de sépultures à Caen',
    description:
      "Consultez les avis clients sur nos prestations d'entretien et de nettoyage de sépultures à Caen et ses alentours.",
    path: '/avis',
    keywords: ['avis', 'témoignages', 'sépulture', 'tombe', 'entretien', 'nettoyage', 'Caen'],
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'Avis', path: '/avis' },
  ]);
  const aggregateRatingJsonLd =
    reviews.length > 0
      ? {
          '@context': 'https://schema.org',
          '@type': 'LocalBusiness',
          name: seoConfig.siteName,
          url: `${seoConfig.siteUrl}/avis`,
          aggregateRating: {
            '@type': 'AggregateRating',
            ratingValue: Number(averageRating.toFixed(2)),
            reviewCount: reviews.length,
            bestRating: 5,
            worstRating: 1,
          },
        }
      : null;

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Les avis de nos clients"
        subtitle="Des retours authentiques sur nos interventions d'entretien de sépultures."
        imageAlt="Avis clients sur les prestations de Lueur d'Éternité"
      />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center gap-8">
          <h2 className="section-heading text-3xl lg:text-4xl">Ils nous font confiance</h2>
          {reviews.length === 0 ? (
            <p className="text-center italic text-[rgba(43,43,43,.75)]">Aucun avis publié pour le moment.</p>
          ) : (
            <div className="reviews-list">
              {reviews.map((review) => (
                <CardComponent
                  key={review.id}
                  className="reviews-list__item !items-start !justify-start !gap-2 !text-left"
                >
                  <div className="reviews-loop__head w-full flex items-center justify-between gap-4">
                    <strong>{review.author}</strong>
                    <span
                      className="reviews-loop__rate flex items-center gap-1"
                      aria-label={`Note ${review.rate} sur 5`}
                    >
                      {[1, 2, 3, 4, 5].map((value) => (
                        <Star
                          key={value}
                          aria-hidden
                          className={`h-4 w-4 ${value <= Math.max(1, Math.min(5, review.rate)) ? 'reviews-star--filled' : 'reviews-star--empty'}`}
                        />
                      ))}
                    </span>
                  </div>
                  {review.title ? <h3 className="reviews-loop__title">{review.title}</h3> : null}
                  <p className="reviews-loop__comment">{review.comment}</p>
                </CardComponent>
              ))}
            </div>
          )}
        </div>
      </section>

      <SectionDivider />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center gap-6 text-center">
          <h3 className="text-3xl lg:text-4xl">Et vous ? racontez-nous votre expérience !</h3>
          <p className="max-w-2xl text-center italic">
            Votre retour compte. Une fois envoyé, votre avis sera publié après validation.
          </p>
          <div className="w-full max-w-3xl rounded-[var(--radius-lg)] border border-[var(--border)] bg-[var(--card)] p-5 shadow-xl lg:p-7">
            <ReviewForm />
          </div>
        </div>
      </section>

      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(breadcrumbJsonLd) }} />
      {aggregateRatingJsonLd ? (
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(aggregateRatingJsonLd) }} />
      ) : null}
    </>
  );
}
