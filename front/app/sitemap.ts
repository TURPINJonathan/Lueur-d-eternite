import type { MetadataRoute } from 'next';
import { seoConfig } from './seo';

const routes = [
  '/',
  '/services',
  '/tarifs',
  '/galerie',
  '/a-propos',
  '/contact',
  '/mentions-legales',
  '/politique-de-confidentialite',
] as const;

export default function sitemap(): MetadataRoute.Sitemap {
  const now = new Date();

  return routes.map((route) => ({
    url: `${seoConfig.siteUrl}${route}`,
    lastModified: now,
    changeFrequency: route === '/' ? 'weekly' : 'monthly',
    priority: route === '/' ? 1 : 0.8,
  }));
}
