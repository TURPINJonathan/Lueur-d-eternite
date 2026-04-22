import { apiGet } from '#lib/api';

export interface PublicReview {
  id: string;
  author: string;
  title: string | null;
  comment: string;
  rate: number;
  created_at: string | null;
}

export async function getReviews(revalidate: number = 120): Promise<PublicReview[]> {
  try {
    return await apiGet<PublicReview[]>('/api/public/reviews', { revalidate });
  } catch (error) {
    console.error('GET /api/public/reviews failed:', error);
    return [];
  }
}
