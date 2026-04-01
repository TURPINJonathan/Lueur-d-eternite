import type { GalleryItem } from '#components/ui/Gallery.component';

import { apiGet } from '#lib/api';

export async function getGalleryItems(revalidate: number = 60): Promise<GalleryItem[]> {
  const baseUrl = process.env.NEXT_PUBLIC_BACK_BASE_URL ?? '';
  return apiGet<GalleryItem[]>(`${baseUrl.replace(/\/+$/, '')}/api/public/gallery-items`, { revalidate });
}
