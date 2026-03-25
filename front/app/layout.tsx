import type { Metadata } from 'next';
import { Cormorant_Garamond, Inter, Playfair_Display } from 'next/font/google';
import './globals.css';
import '../styles/index.scss';
import { Footer, Header, NavigationRouteLoader } from '#components';
import { seoConfig } from './seo';
import { buildWebSiteJsonLd } from './seo-jsonld';
import { safeJsonLd } from './jsonld';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

const cormorand = Cormorant_Garamond({
  subsets: ['latin'],
  variable: '--font-cormorant',
  display: 'swap',
});

const playfair = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair',
  display: 'swap',
});

export const metadata: Metadata = {
  metadataBase: new URL(seoConfig.siteUrl),
  title: {
    default: seoConfig.defaultTitle,
    template: `%s | ${seoConfig.siteName}`,
  },
  description: seoConfig.defaultDescription,
  applicationName: seoConfig.siteName,
  keywords: [
    'entretien sépulture Caen',
    'nettoyage tombe Caen',
    'nettoyage tombe Calvados',
    'soin sépulture Caen',
    'fleurissement sépulture',
    'service funéraire Caen',
    'entretien tombe Normandie',
  ],
  alternates: {
    canonical: '/',
  },
  robots: {
    index: true,
    follow: true,
    googleBot: {
      index: true,
      follow: true,
      'max-image-preview': 'large',
      'max-snippet': -1,
      'max-video-preview': -1,
    },
  },
  openGraph: {
    type: 'website',
    locale: seoConfig.locale,
    url: seoConfig.siteUrl,
    siteName: seoConfig.siteName,
    title: seoConfig.defaultTitle,
    description: seoConfig.defaultDescription,
    images: [
      {
        url: seoConfig.defaultImage,
        width: 1200,
        height: 630,
        alt: seoConfig.siteName,
      },
    ],
  },
  twitter: {
    card: 'summary_large_image',
    title: seoConfig.defaultTitle,
    description: seoConfig.defaultDescription,
    images: [seoConfig.defaultImage],
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const localBusinessJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'LocalBusiness',
    name: seoConfig.siteName,
    image: `${seoConfig.siteUrl}${seoConfig.defaultImage}`,
    url: seoConfig.siteUrl,
    telephone: seoConfig.phoneHref,
    email: seoConfig.email,
    address: {
      '@type': 'PostalAddress',
      addressLocality: seoConfig.city,
      addressRegion: seoConfig.region,
      addressCountry: 'FR',
    },
    areaServed: {
      '@type': 'AdministrativeArea',
      name: 'Calvados',
    },
    description: seoConfig.defaultDescription,
    knowsAbout: ['entretien de sépulture', 'nettoyage de tombe', 'soin de sépulture', 'fleurissement'],
    openingHoursSpecification: [
      { '@type': 'OpeningHoursSpecification', dayOfWeek: 'Monday', opens: '08:00', closes: '18:00' },
      { '@type': 'OpeningHoursSpecification', dayOfWeek: 'Tuesday', opens: '08:00', closes: '18:00' },
      { '@type': 'OpeningHoursSpecification', dayOfWeek: 'Wednesday', opens: '08:00', closes: '18:00' },
      { '@type': 'OpeningHoursSpecification', dayOfWeek: 'Thursday', opens: '08:00', closes: '18:00' },
      { '@type': 'OpeningHoursSpecification', dayOfWeek: 'Friday', opens: '08:00', closes: '18:00' },
    ],
    hasOfferCatalog: {
      '@type': 'OfferCatalog',
      name: 'Pages & prestations : entretien de sépultures à Caen (Calvados)',
      itemListElement: [
        {
          '@type': 'Offer',
          name: 'Accueil - entretien de sépultures à Caen (Calvados)',
          url: `${seoConfig.siteUrl}/`,
          description: 'Présentation du service local : nettoyage, entretien et soins respectueux.',
        },
        {
          '@type': 'Offer',
          name: 'Services - nettoyage et soin de tombe',
          url: `${seoConfig.siteUrl}/services`,
          description: 'Nettoyage en profondeur, entretien régulier et options complémentaires.',
        },
        {
          '@type': 'Offer',
          name: 'Tarifs - formules et estimation',
          url: `${seoConfig.siteUrl}/tarifs`,
          description: 'Consultez nos tarifs et formules pour l’entretien des sépultures.',
        },
        {
          '@type': 'Offer',
          name: 'Galerie - avant / après',
          url: `${seoConfig.siteUrl}/galerie`,
          description: 'Exemples de réalisations : entretien, nettoyage et soin de tombes.',
        },
        {
          '@type': 'Offer',
          name: 'À propos - valeurs & méthode',
          url: `${seoConfig.siteUrl}/a-propos`,
          description: 'L’histoire, l’approche et l’engagement du service à Caen et alentours.',
        },
        {
          '@type': 'Offer',
          name: 'Contact - demander un devis',
          url: `${seoConfig.siteUrl}/contact`,
          description: 'Formulaire de contact et coordonnées pour demander un devis.',
        },
      ],
    },
  };
  const websiteJsonLd = buildWebSiteJsonLd();

  return (
    <html lang="fr" className={`${cormorand.variable} ${playfair.variable} ${inter.variable}`}>
      <body className="font-body">
        <a href="#main-content" className="skip-link">
          Aller au contenu principal
        </a>
        <Header />
        <main id="main-content" className="paper-grain">
          {children}
        </main>
        <Footer />
        <NavigationRouteLoader />
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(websiteJsonLd) }} />
        <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(localBusinessJsonLd) }} />
      </body>
    </html>
  );
}
