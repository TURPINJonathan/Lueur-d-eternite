import type { Metadata } from 'next';
import { ButtonComponent, CardComponent, HeroComponent, SectionDivider } from '#ui';
import MapComponent from '#/components/ui/Map.dynamic';
import { Clock, HeartHandshake, ShieldCheck } from 'lucide-react';
import HeroPicture from '../public/assets/home_hero_picture.webp';
import { createPageMetadata } from './seo';
import { buildBreadcrumbJsonLd, buildFaqJsonLd, buildServiceJsonLd, buildWebPageJsonLd } from './seo-jsonld';
import Link from 'next/link';

export const metadata: Metadata = createPageMetadata({
  title: "Entretien de sépultures à Caen",
  description:
    "Nous entretenons les sépultures à Caen et alentours avec soin, régularité et respect, pour préserver durablement les lieux de mémoire.",
  path: '/',
});

export default function Home() {
  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Entretien de sépultures à Caen',
    description:
      "Nous entretenons les sépultures à Caen et alentours avec soin, régularité et respect, pour préserver durablement les lieux de mémoire.",
    path: '/',
  });
  const breadcrumbJsonLd = buildBreadcrumbJsonLd([{ name: 'Accueil', path: '/' }]);
  const serviceJsonLd = buildServiceJsonLd();
  const faqJsonLd = buildFaqJsonLd([
    {
      question: "Dans quelle zone intervenez-vous autour de Caen ?",
      answer:
        "Nous intervenons à Caen et dans les communes alentours, avec une zone d'intervention d'environ 15 km autour de la ville.",
    },
    {
      question: "Proposez-vous un entretien ponctuel et régulier ?",
      answer:
        "Oui, nous proposons un nettoyage initial en profondeur puis des entretiens réguliers selon la fréquence souhaitée.",
    },
    {
      question: "Comment obtenir un devis personnalisé ?",
      answer:
        "Vous pouvez nous contacter par téléphone ou via la page contact pour recevoir un devis adapté à votre besoin.",
    },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Nous entretenons les sépultures de vos proches pour que chaque souvenir perdure."
        subtitle="Service professionnel de nettoyage et d'entretien de sépultures à Caen et alentours"
        priority
        imageAlt="Sépulture entretenue avec soin à Caen"
        ctaPrimary={
          <ButtonComponent href="/contact" variant="gold" size="xlf">
            Contactez-nous
          </ButtonComponent>
        }
        ctaSecondary={
          <ButtonComponent href="/services" variant="goldSecondary" size="xlf">
            Découvrez nos services
          </ButtonComponent>
        }
      />

      <section className="page-shell page-section why-section">
        <div className="flex flex-col items-center gap-8">
          <h2 className="section-heading text-3xl lg:text-4xl">Pourquoi choisir nos services ?</h2>

          <div className="w-full flex gap-5 flex-col lg:flex-row items-center">
            <CardComponent
              icon={Clock}
              title="Ponctualité"
              description="Interventions planifiées et réalisées dans les délais convenus."
              className="flex-1 basis-[250px]"
            />

            <CardComponent
              icon={HeartHandshake}
              title="Respect"
              description="Une approche délicate et respectueuse de la mémoire de vos proches."
              premium
              className="flex-[1.08] basis-[250px]"
            />

            <CardComponent
              icon={ShieldCheck}
              title="Professionnalisme"
              description="Un service de qualité avec des produits adaptés et respectueux."
              className="flex-1 basis-[250px]"
            />
          </div>
        </div>
      </section>

      <SectionDivider />

      <section className="page-shell page-section flex flex-wrap items-center gap-8">
        <div className="flex-2 basis-[420px] flex flex-col gap-4">
          <h3 className="text-3xl lg:text-4xl">Un service local, organisé et fiable</h3>
          <p className="leading-6">
            Nous intervenons à Caen et ses environs afin de veiller à entretenir les sépultures pour que chaque lieu
            reste digne, soigné et apaisant au fil du temps.
            <br />
            Un service simple, pensé pour vous accompagner sans contrainte.
          </p>
          <p className="leading-6">
            Découvrez le détail de nos <Link href="/services" className="underline underline-offset-4">prestations</Link> et
            consultez nos <Link href="/tarifs" className="underline underline-offset-4">tarifs d&apos;entretien</Link> pour
            choisir la formule la plus adaptée.
          </p>
        </div>

        <div className="flex-3 basis-[600px]">
          <MapComponent mode="circle" center={[49.1829, -0.3707]} radiusKm={15} zoomBoost={1} />
        </div>
      </section>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(breadcrumbJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(serviceJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(faqJsonLd) }} />
    </>
  );
}
