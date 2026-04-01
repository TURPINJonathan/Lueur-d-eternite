import type { Metadata } from 'next';
import { ButtonComponent, HeroComponent, SectionDivider } from '#ui';
import HeroPicture from '../../public/assets/service_hero_picture.webp';
import { CheckCircle } from 'lucide-react';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildFaqJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import Image from 'next/image';
import Link from 'next/link';
import { safeJsonLd } from '../jsonld';
import { getServices, type ServiceCard } from '#lib';

export const metadata: Metadata = createPageMetadata({
  title: 'Services nettoyage tombe | Caen (Calvados)',
  description:
    'Découvrez nos prestations de nettoyage et soin de sépultures (tombes) : nettoyage en profondeur, entretien régulier et options complémentaires à Caen (Calvados) et alentours.',
  path: '/services',
  keywords: ['sépulture', 'tombe', 'nettoyage', 'soin', 'entretien', 'Caen', 'Calvados'],
});

export default async function Services() {
  let services: ServiceCard[] = [];
  try {
    services = await getServices(60);
  } catch {
    services = [];
  }

  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Services nettoyage tombe | Caen (Calvados)',
    description:
      'Découvrez nos prestations de nettoyage et soin de sépultures (tombes) : nettoyage en profondeur, entretien régulier et options complémentaires à Caen (Calvados) et alentours.',
    path: '/services',
    keywords: ['sépulture', 'tombe', 'nettoyage', 'soin', 'entretien', 'Caen', 'Calvados'],
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'Services', path: '/services' },
  ]);
  const faqJsonLd = buildFaqJsonLd([
    {
      question: 'Que comprend le nettoyage en profondeur ?',
      answer:
        'Le nettoyage en profondeur comprend un soin complet de la pierre, des ornements et des détails avec des produits adaptés.',
    },
    {
      question: 'Puis-je choisir uniquement certaines prestations ?',
      answer:
        'Oui, vous pouvez sélectionner des services complémentaires selon les besoins spécifiques de la sépulture.',
    },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Nos services"
        subtitle="Des prestations professionnelles pour l'entretien et l'embellissement des sépultures à Caen et alentours"
        imageAlt="Nettoyage professionnel de sépulture en Normandie"
      />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center justify-center gap-10 lg:gap-8">
          {services.length === 0 ? (
            <div className="mt-6 text-center text-sm text-[rgba(43,43,43,.75)]">
              Services en cours de construction, revenez bientôt.
            </div>
          ) : (
            services.map((service, index) => {
              const imageOnLeft = index % 2 === 0;
              const imageOrder = imageOnLeft ? 'lg:order-1' : 'lg:order-2';
              const contentOrder = imageOnLeft ? 'lg:order-2' : 'lg:order-1';

              return (
                <div key={service.id} className="w-full flex flex-col lg:flex-row gap-5 lg:gap-10">
                  <div className={`services-image-frame !p-0 relative overflow-hidden flex-1 ${imageOrder}`}>
                    {service.picture ? (
                      <Image
                        src={service.picture}
                        alt={service.pictureAlt.trim()}
                        className="absolute inset-0 h-full w-full object-cover"
                        loading="lazy"
                        decoding="async"
                        sizes="(max-width: 1024px) 100vw, 50vw"
                        fill
                      />
                    ) : null}
                  </div>

                  <div className={`flex-1 flex flex-col justify-center gap-3 pb-4 lg:py-4 ${contentOrder}`}>
                    <h3 className="text-3xl lg:text-4xl px-5">{service.title}</h3>
                    <p className="leading-6 italic px-5">{service.subtitle}</p>
                    <ul className="px-5">
                      {service.items.map((item, itemIdx) => (
                        <li key={`${service.id}-${itemIdx}`} className="flex justify-start items-center mb-1 gap-2">
                          <CheckCircle className="h-5 w-5" />
                          <span className="leading-6">{item}</span>
                        </li>
                      ))}
                    </ul>
                  </div>
                </div>
              );
            })
          )}
        </div>
      </section>

      <SectionDivider flip />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center gap-8">
          <h3 className="section-heading text-3xl lg:text-4xl">Vous souhaitez des renseignements complémentaires ?</h3>
          <p className="leading-6 text-center flex flex-col gap-2 justify-center items-center">
            <span>N&apos;hésitez pas à nous contacter pour obtenir de plus amples informations.</span>
            <span>
              Pour estimer votre budget, consultez également notre page{' '}
              <Link href="/tarifs" className="underline underline-offset-4">
                tarifs
              </Link>
              .
            </span>
          </p>

          <ButtonComponent href="/contact" variant="gold" size="xl">
            Contactez-nous
          </ButtonComponent>
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(breadcrumbJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(faqJsonLd) }} />
    </>
  );
}
