import { apiGet } from '#lib/api';

export interface TarifCard {
  id: string;
  title: string;
  details: string;
  priceCents: number;
  originalPriceCents: number;
  hasDiscount: boolean;
  discountLabel: string | null;
  offerType: 'promotion' | 'promo_code' | null;
  offerName: string | null;
  offerCode: string | null;
  position: number;
}

export interface TarifGenericNotice {
  kind: 'promotion' | 'promo_code';
  title: string;
  label: string;
  code: string | null;
}

export interface TarifsResponse {
  items: TarifCard[];
  genericNotices: TarifGenericNotice[];
}

export async function getTarifs(revalidate: number = 60): Promise<TarifsResponse> {
  const baseUrl = process.env.NEXT_PUBLIC_BACK_BASE_URL ?? '';
  return apiGet<TarifsResponse>(`${baseUrl.replace(/\/+$/, '')}/api/public/tarifs`, { revalidate });
}
