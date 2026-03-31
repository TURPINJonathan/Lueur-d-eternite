import { apiGet } from './api';

export interface SiteSettingsPublic {
  contactPhoneDisplay: string;
  contactEmail: string;
  serviceRadiusKm: number;
  serviceAreaText: string;
  legalZoneNotice: string;
  legalEntityName: string;
  legalStatus: string;
  legalAddress: string;
  legalSiren: string;
  legalSiret: string;
  legalVat: string;
  publicationDirector: string;
  hostingProviderName: string;
  hostingProviderAddress: string;
  hostingProviderUrl: string;
  technicalConfig: string;
  updatedAt: string;
}

export const DEFAULT_SITE_SETTINGS: SiteSettingsPublic = {
  contactPhoneDisplay: '06 25 29 59 52',
  contactEmail: 'contact@lueur-eternite.fr',
  serviceRadiusKm: 15,
  serviceAreaText: 'Caen et ses alentours',
  legalZoneNotice: 'Prestations limitées à 15 km autour de Caen.',
  legalEntityName: 'Émilie SIMON',
  legalStatus: 'Entrepreneur individuel',
  legalAddress: '49 rue de Condé, 14220 Thury-Harcourt-le-Hom, France',
  legalSiren: '848 739 546',
  legalSiret: '848 739 546 00036',
  legalVat: 'TVA non applicable, article 293B du CGI',
  publicationDirector: 'Émilie SIMON',
  hostingProviderName: 'OVHcloud',
  hostingProviderAddress: '2 rue Kellermann, 59100 Roubaix, France',
  hostingProviderUrl: 'https://www.ovh.com',
  technicalConfig: '{}',
  updatedAt: new Date(0).toISOString(),
};

export async function getSiteSettings(revalidate: number = 60): Promise<SiteSettingsPublic> {
  try {
    return await apiGet<SiteSettingsPublic>('/api/public/site-settings', { revalidate }).then((data) => ({
      ...DEFAULT_SITE_SETTINGS,
      ...data,
    }));
  } catch (error) {
    console.error('GET /api/public/site-settings failed:', error);
    return DEFAULT_SITE_SETTINGS;
  }
}
