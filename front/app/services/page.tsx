import type { Metadata } from 'next';
import { ButtonComponent, HeroComponent, SectionDivider } from '#ui';
import HeroPicture from '../../public/assets/service_hero_picture.webp';
import picture1 from '../../public/assets/home_hero_picture.webp';
import picture2 from '../../public/assets/pricing_hero_picture.webp';
import picture3 from '../../public/assets/contact_hero_picture.webp';
import { CheckCircle } from 'lucide-react';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildFaqJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import Link from 'next/link';
import { safeJsonLd } from '../jsonld';

export const metadata: Metadata = createPageMetadata({
  title: 'Services nettoyage tombe | Caen (Calvados)',
  description:
    'Découvrez nos prestations de nettoyage et soin de sépultures (tombes) : nettoyage en profondeur, entretien régulier et options complémentaires à Caen (Calvados) et alentours.',
  path: '/services',
  keywords: ['sépulture', 'tombe', 'nettoyage', 'soin', 'entretien', 'Caen', 'Calvados'],
});

const services = [
  {
    picture: picture1,
    title: 'Nettoyage en profondeur',
    subtitle: 'Pour un éclat des premiers jours',
    items: ['Produits spécifiques', 'Nettoyage intensif', 'Ornement', 'Détails'],
  },
  {
    picture: picture2,
    title: 'Nettoyage simple',
    subtitle: 'Un suivi soigné',
    items: ['Détails'],
  },
  {
    picture: picture3,
    title: 'Des services complémentaires',
    subtitle: 'Pour des détails minutieux',
    items: ['Détails'],
  },
];

export default function Services() {
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
          {services.map((service, index) => {
            const imageOnLeft = index % 2 === 0;
            const imageOrder = imageOnLeft ? 'lg:order-1' : 'lg:order-2';
            const contentOrder = imageOnLeft ? 'lg:order-2' : 'lg:order-1';

            return (
              <div key={service.title} className="w-full flex flex-col lg:flex-row gap-5 lg:gap-10">
                <div
                  className={`services-image-frame !p-0 bg-no-repeat bg-cover bg-center flex-1 ${imageOrder}`}
                  style={{ backgroundImage: `url(${service.picture.src})` }}
                  role="img"
                  aria-label={service.title}
                />

                <div className={`flex-1 flex flex-col justify-center gap-3 pb-4 lg:py-4 ${contentOrder}`}>
                  <h3 className="text-3xl lg:text-4xl px-5">{service.title}</h3>
                  <p className="leading-6 italic px-5">{service.subtitle}</p>
                  <ul className="px-5">
                    {service.items.map((item) => (
                      <li key={item} className="flex justify-start items-center mb-1 gap-2">
                        <CheckCircle className="h-5 w-5" />
                        <span className="leading-6">{item}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              </div>
            );
          })}
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
