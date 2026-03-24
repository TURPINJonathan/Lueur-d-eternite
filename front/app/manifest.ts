import type { MetadataRoute } from 'next';
import { seoConfig } from './seo';

export default function manifest(): MetadataRoute.Manifest {
  return {
    name: seoConfig.siteName,
    short_name: 'Lueur',
    description: seoConfig.defaultDescription,
    start_url: '/',
    display: 'standalone',
    background_color: '#efe7db',
    theme_color: '#9f7b22',
    lang: 'fr',
    icons: [
      {
        src: '/favicon.ico',
        sizes: 'any',
        type: 'image/x-icon',
      },
    ],
  };
}
