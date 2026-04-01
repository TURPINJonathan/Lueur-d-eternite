import { apiGet } from '#lib/api';

export interface ServiceCard {
  id: string;
  title: string;
  subtitle: string;
  items: string[];
  picture: string | null;
  /** Description de l’image (colonne `picture_alt`), saisie en back-office — utilisée pour l’attribut `alt` côté front. */
  pictureAlt: string;
}

export async function getServices(revalidate: number = 60): Promise<ServiceCard[]> {
  const baseUrl = process.env.NEXT_PUBLIC_BACK_BASE_URL ?? '';
  return apiGet<ServiceCard[]>(`${baseUrl.replace(/\/+$/, '')}/api/public/services`, { revalidate });
}
