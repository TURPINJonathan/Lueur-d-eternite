import type { Metadata } from 'next';
import { CardComponent, ContactFormComponent, HeroComponent } from '#ui';
import { MailCheck, PhoneCall } from 'lucide-react';
import HeroPicture from '../../public/assets/contact_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import { safeJsonLd } from '../jsonld';

export const metadata: Metadata = createPageMetadata({
  title: 'Devis nettoyage tombe | Caen (Calvados)',
  description:
    "Contactez Lueur d'Éternité pour un devis ou des renseignements sur l'entretien de sépultures à Caen et alentours.",
  path: '/contact',
  keywords: ['devis', 'contact', 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
});

export default function Contact() {
  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Devis nettoyage tombe | Caen (Calvados)',
    description:
      "Contactez Lueur d'Éternité pour un devis ou des renseignements sur l'entretien de sépultures à Caen et alentours.",
    path: '/contact',
    keywords: ['devis', 'contact', 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'Contact', path: '/contact' },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Contactez-nous"
        subtitle="Nous répondons avec attention à vos besoins"
        imageAlt="Prise de contact pour entretien de sépulture"
      />

      <section className="page-shell page-section flex flex-wrap gap-8">
        <aside className="order-2 flex flex-1 basis-[200px] flex-col items-start justify-start gap-4 lg:order-1">
          <a href="tel:+33625295952" className="w-full flex-1">
            <CardComponent icon={PhoneCall} title="Téléphone" description="06 25 29 59 52" className="w-full h-full">
              <p className="text-center text-sm italic">
                Du lundi au vendredi
                <br />
                de 9h00 à 18h00
              </p>
            </CardComponent>
          </a>

          <a href="mailto:contact@lueur-eternite.fr" className="w-full flex-1">
            <CardComponent
              icon={MailCheck}
              title="Email"
              description="contact@lueur-eternite.fr"
              className="w-full h-full"
            >
              <p className="text-sm italic">Réponse sous 48h !</p>
            </CardComponent>
          </a>
        </aside>

        <div className="flex-1 basis-[600px] order-1 lg:order-2 rounded-[var(--radius-lg)] border border-[var(--border)] p-5 lg:p-7 bg-[var(--card)] shadow-xl">
          <ContactFormComponent />
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(breadcrumbJsonLd) }} />
    </>
  );
}
