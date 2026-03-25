import { seoConfig } from './seo';

interface BreadcrumbItem {
  name: string;
  path: string;
}

export function buildWebSiteJsonLd() {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebSite',
    name: seoConfig.siteName,
    url: seoConfig.siteUrl,
    inLanguage: 'fr-FR',
  };
}

export function buildWebPageJsonLd({
  title,
  description,
  path,
  keywords,
}: {
  title: string;
  description: string;
  path: string;
  keywords?: string[];
}) {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: title,
    description,
    keywords: keywords?.join(', '),
    url: `${seoConfig.siteUrl}${path}`,
    inLanguage: 'fr-FR',
    isPartOf: {
      '@type': 'WebSite',
      name: seoConfig.siteName,
      url: seoConfig.siteUrl,
    },
  };
}

export function buildBreadcrumbJsonLd(items: BreadcrumbItem[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: items.map((item, index) => ({
      '@type': 'ListItem',
      position: index + 1,
      name: item.name,
      item: `${seoConfig.siteUrl}${item.path}`,
    })),
  };
}

export function buildServiceJsonLd() {
  return {
    '@context': 'https://schema.org',
    '@type': 'Service',
    name: 'Entretien, nettoyage et soin de sépultures (tombes) à Caen (Calvados)',
    provider: {
      '@type': 'LocalBusiness',
      name: seoConfig.siteName,
      url: seoConfig.siteUrl,
      telephone: seoConfig.phoneHref,
      email: seoConfig.email,
    },
    areaServed: [
      {
        '@type': 'AdministrativeArea',
        name: 'Calvados',
        addressCountry: 'FR',
      },
      {
        '@type': 'City',
        name: seoConfig.city,
        addressCountry: 'FR',
      },
      {
        '@type': 'GeoCircle',
        geoMidpoint: {
          '@type': 'GeoCoordinates',
          latitude: 49.1829,
          longitude: -0.3707,
        },
        geoRadius: seoConfig.serviceRadiusKm * 1000,
      },
    ],
    serviceType: [
      'Nettoyage de tombe',
      'Soin de sépulture',
      'Nettoyage en profondeur de sépulture',
      'Entretien régulier de tombe',
      'Fleurissement et soins complémentaires',
    ],
    availableChannel: {
      '@type': 'ServiceChannel',
      serviceUrl: `${seoConfig.siteUrl}/contact`,
    },
  };
}

export function buildFaqJsonLd(
  entries: Array<{
    question: string;
    answer: string;
  }>,
) {
  return {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: entries.map((entry) => ({
      '@type': 'Question',
      name: entry.question,
      acceptedAnswer: {
        '@type': 'Answer',
        text: entry.answer,
      },
    })),
  };
}
