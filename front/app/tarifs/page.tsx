import type { Metadata } from 'next';
import { CardComponent, HeroComponent, SectionDivider } from '#ui';
import { ConciergeBell, Euro, Handshake, MapPinCheckInside, ReceiptEuro } from 'lucide-react';
import HeroPicture from '../../public/assets/pricing_hero_picture.webp';
import { createPageMetadata } from '../seo';
import { buildBreadcrumbJsonLd, buildWebPageJsonLd } from '../seo-jsonld';
import { safeJsonLd } from '../jsonld';
import { getTarifs, type TarifCard, type TarifGenericNotice } from '#lib';

export const metadata: Metadata = createPageMetadata({
  title: 'Tarifs entretien sépulture | Caen (Calvados)',
  description:
    "Consultez nos tarifs d'entretien de sépultures à Caen : nettoyage initial, entretien régulier et options complémentaires.",
  path: '/tarifs',
  keywords: ['tarifs', 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
});

function formatEuroFromCents(priceCents: number): string {
  const abs = Math.abs(priceCents);
  const euros = Math.floor(abs / 100);
  const cents = abs % 100;

  if (cents === 0) return euros.toString();
  return `${euros},${cents.toString().padStart(2, '0')}`;
}

function GlobalNotices({ notices }: { notices: TarifGenericNotice[] }) {
  if (notices.length === 0) return null;

  return (
    <aside
      className={`flex-1 basis-[200px] w-full flex flex-row flex-wrap justify-center items-center gap-4 ${notices.length === 1 ? 'lg:flex-col' : ''}`}
    >
      {notices.map((notice, idx) => (
        <CardComponent
          key={`${notice.kind}-${notice.title}-${idx}`}
          className="flex-1 basis-[250px]"
          premium={(notices.length <= 3 && idx === 1) || (notices.length === 1 && idx === 0) || notices.length === 2}
          title={notice.label}
          description={notice.title}
        >
          {/* <pre>{JSON.stringify(notice, null, 2)}</pre> */}
          {notice.code && (
            <span className="inline-flex items-center rounded border border-[var(--color-gold)]/40 bg-[var(--muted)] px-3 py-1 text-xs font-medium uppercase tracking-[0.04em] text-[var(--foreground)]">
              {notice.code}
            </span>
          )}
        </CardComponent>
      ))}
    </aside>
  );
}

function DesktopPriceCell({ tarif }: { tarif: TarifCard }) {
  return (
    <div className="flex flex-col items-start gap-1.5">
      <div className="flex items-center gap-2">
        {tarif.hasDiscount && (
          <span className="text-sm text-[var(--muted-foreground)] line-through decoration-[var(--color-gold)] decoration-[1px]">
            {formatEuroFromCents(tarif.originalPriceCents)} €
          </span>
        )}

        <span className="flex items-center gap-1 font-semibold text-[var(--color-gold)]">
          <span className="leading-none">{formatEuroFromCents(tarif.priceCents)}</span>
          <Euro className="h-4 w-4" />
        </span>
      </div>
    </div>
  );
}

function MobilePriceCell({ tarif }: { tarif: TarifCard }) {
  return (
    <div className="flex flex-col items-center gap-1.5">
      <div className="flex items-end gap-2">
        {tarif.hasDiscount ? (
          <span className="text-xs text-[var(--muted-foreground)] line-through decoration-[var(--color-gold)] decoration-[1px]">
            {formatEuroFromCents(tarif.originalPriceCents)} €
          </span>
        ) : null}

        <span className="flex items-center gap-1.5 font-semibold text-[var(--color-gold)]">
          <span className="text-xl leading-none">{formatEuroFromCents(tarif.priceCents)}</span>
          <Euro className="h-4 w-4 !text-[var(--color-gold)]" />
        </span>
      </div>
    </div>
  );
}

export default async function Pricing() {
  let tarifs: TarifCard[] = [];
  let genericNotices: TarifGenericNotice[] = [];
  let apiError: string | null = null;

  try {
    const data = await getTarifs(60);
    tarifs = data.items;
    genericNotices = data.genericNotices;
  } catch (e) {
    console.error('GET /api/public/tarifs failed:', e);
    apiError = e instanceof Error ? e.message : 'Impossible de charger les tarifs depuis le back.';
    tarifs = [];
    genericNotices = [];
  }

  const webPageJsonLd = buildWebPageJsonLd({
    title: 'Tarifs entretien sépulture | Caen (Calvados)',
    description:
      "Consultez nos tarifs d'entretien de sépultures à Caen : nettoyage initial, entretien régulier et options complémentaires.",
    path: '/tarifs',
    keywords: ['tarifs', 'sépulture', 'tombe', 'nettoyage', 'soin', 'Caen', 'Calvados'],
  });

  const breadcrumbJsonLd = buildBreadcrumbJsonLd([
    { name: 'Accueil', path: '/' },
    { name: 'Tarifs', path: '/tarifs' },
  ]);

  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Nos tarifs"
        subtitle="Le respect de nos défunts ne doit pas être un coût"
        imageAlt="Tarifs pour entretien de sépultures à Caen"
      />

      <section className="page-shell page-section">
        <div className={`flex items-center flex-col gap-8 ${genericNotices.length === 1 ? 'lg:flex-row' : 'flex-col'}`}>
          <div
            className={`w-full flex-4 overflow-hidden rounded-[var(--radius-lg)] shadow-xl ${genericNotices.length === 1 ? ' lg:basis-[700px]' : ''}`}
          >
            {tarifs.length === 0 ? (
              <>
                <div className="p-8 text-center text-sm">Tarifs en cours de construction, revenez bientôt.</div>
                {apiError && process.env.NODE_ENV === 'development' ? (
                  <div className="mt-2 text-center text-xs text-red-600">{apiError}</div>
                ) : null}
              </>
            ) : (
              <>
                <div className="hidden md:block">
                  <table className="w-full rounded border-collapse">
                    <thead>
                      <tr className="border-b border-[#d7bf86]/35 bg-[linear-gradient(120deg,#f5e6c4_0%,#e7cf9a_50%,#d4af37_100%)]">
                        <th className="px-6 py-5 text-left text-sm font-semibold text-[var(--foreground)]">Formules</th>
                        <th className="px-6 py-5 text-left text-sm font-semibold text-[var(--foreground)]">
                          Précisions
                        </th>
                        <th className="px-6 py-5 text-left text-sm font-semibold text-[var(--foreground)]">Prix</th>
                      </tr>
                    </thead>

                    <tbody>
                      {tarifs.map((tarif, index) => (
                        <tr
                          key={tarif.id}
                          className={`transition-colors duration-200 hover:bg-[#f9f5ec] ${
                            index !== tarifs.length - 1 ? 'border-b border-[#d9ccb2]/55' : ''
                          }`}
                        >
                          <td className="px-6 py-6 align-top">
                            <span className="font-semibold text-[var(--foreground)]">{tarif.title}</span>
                          </td>

                          <td className="px-6 py-6 align-top">
                            <span className="italic text-[var(--muted-foreground)]">{tarif.details}</span>
                          </td>

                          <td className="px-6 py-6 align-top text-[var(--foreground)]">
                            <DesktopPriceCell tarif={tarif} />
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>

                <div className="flex flex-col gap-5 p-4 md:hidden">
                  {tarifs.map((tarif) => (
                    <CardComponent
                      key={tarif.id}
                      className="!min-h-lg !items-center !justify-center !gap-2 !p-5 !text-center"
                    >
                      <div className="flex flex-col items-center gap-3">
                        <h3 className="text-2xl font-medium text-[var(--foreground)]">{tarif.title}</h3>
                        <h4 className="!text-xs !font-light italic text-[var(--foreground)]">{tarif.details}</h4>
                        <MobilePriceCell tarif={tarif} />
                      </div>
                    </CardComponent>
                  ))}
                </div>
              </>
            )}
          </div>

          <GlobalNotices notices={genericNotices} />
        </div>
      </section>

      <SectionDivider />

      <section className="page-shell page-section">
        <div className="flex flex-wrap gap-8">
          <div className="flex-1 basis-[600px] text-center flex flex-wrap gap-8">
            <CardComponent
              icon={ConciergeBell}
              title="Des prestations à la carte"
              description="Parce que chaque besoin est unique, vous êtes libre de vouloir des prestations complémentaires"
              className="flex-1 basis-[450px]"
            />

            <CardComponent
              icon={MapPinCheckInside}
              title="Zone de prestation"
              description="Nous intervenons à Caen et ses alentours afin de prendre le temps de soigner vos sépultures avec respect."
              className="flex-1 basis-[450px]"
            />
          </div>

          <div className="flex-1 basis-[600px] text-center flex flex-wrap gap-8">
            <CardComponent
              icon={ReceiptEuro}
              title="Une tarification pensée"
              description="Parce que prendre soin de ses proches ne doit pas être une contrainte financière."
              className="flex-1 basis-[450px]"
            />

            <div className="flex-1 basis-[450px]">
              <CardComponent
                icon={Handshake}
                title="Votre satisfaction garantie"
                description="Si le résultat ne vous convient pas, nous revenons gratuitement pour parfaire le travail. Votre satisfaction est notre priorité."
              />
            </div>
          </div>
        </div>
      </section>

      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(webPageJsonLd) }} />
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: safeJsonLd(breadcrumbJsonLd) }} />
    </>
  );
}
