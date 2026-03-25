import type { GalleryItem } from '#/components/ui/Gallery.component';

import { apiGet } from '#/lib/api';

export async function getGalleryItems(revalidate: number = 60): Promise<GalleryItem[]> {
  return apiGet<GalleryItem[]>('/api/public/gallery-items', { revalidate });
}
