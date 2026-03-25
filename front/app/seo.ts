import type { Metadata } from 'next';

const SITE_URL = 'https://lueur-eternite.fr';

export const seoConfig = {
  siteName: "Lueur d'Éternité",
  siteUrl: SITE_URL,
  defaultTitle: "Lueur d'Éternité | Entretien de sépultures à Caen",
  defaultDescription:
    "Service professionnel d'entretien de sépultures à Caen et alentours : nettoyage, fleurissement et suivi respectueux des lieux de mémoire.",
  defaultImage: '/assets/logo_full.webp',
  locale: 'fr_FR',
  phoneDisplay: '06 25 29 59 52',
  phoneHref: '+33625295952',
  email: 'contact@lueur-eternite.fr',
  city: 'Caen',
  region: 'Normandie',
  serviceRadiusKm: 15,
};

export function createPageMetadata({
  title,
  description,
  path,
  keywords,
  noIndex = false,
}: {
  title: string;
  description: string;
  path: string;
  keywords?: string[];
  noIndex?: boolean;
}): Metadata {
  const canonical = `${seoConfig.siteUrl}${path}`;
  return {
    title,
    description,
    keywords,
    alternates: { canonical },
    openGraph: {
      title,
      description,
      url: canonical,
      siteName: seoConfig.siteName,
      locale: seoConfig.locale,
      type: 'website',
      images: [
        {
          url: `${seoConfig.siteUrl}${seoConfig.defaultImage}`,
          width: 1200,
          height: 630,
          alt: seoConfig.siteName,
        },
      ],
    },
    twitter: {
      card: 'summary_large_image',
      title,
      description,
      images: [`${seoConfig.siteUrl}${seoConfig.defaultImage}`],
    },
    robots: noIndex
      ? {
          index: false,
          follow: false,
        }
      : undefined,
  };
}
