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

export function buildWebPageJsonLd({ title, description, path }: { title: string; description: string; path: string }) {
  return {
    '@context': 'https://schema.org',
    '@type': 'WebPage',
    name: title,
    description,
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
    name: 'Entretien de sépultures à Caen et alentours',
    provider: {
      '@type': 'LocalBusiness',
      name: seoConfig.siteName,
      url: seoConfig.siteUrl,
      telephone: seoConfig.phoneHref,
      email: seoConfig.email,
    },
    areaServed: {
      '@type': 'GeoCircle',
      geoMidpoint: {
        '@type': 'GeoCoordinates',
        latitude: 49.1829,
        longitude: -0.3707,
      },
      geoRadius: seoConfig.serviceRadiusKm * 1000,
    },
    serviceType: [
      'Nettoyage en profondeur de sépulture',
      'Entretien régulier de sépulture',
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
