import { CardComponent, HeroComponent, SectionDivider } from '#ui';
import { ConciergeBell, Euro, Handshake, MapPinCheckInside, ReceiptEuro } from 'lucide-react';
import HeroPicture from '../../public/assets/pricing_hero_picture.webp';

interface Price {
  id: string;
  title: string;
  details?: string;
  price: number | string;
}

const prices: Price[] = [
  {
    id: '1',
    title: 'Nettoyage en profondeur',
    details: 'Soin ponctuel, obligatoire pour une première prestation',
    price: 150,
  },
  { id: '2', title: 'Entretien régulier', details: "Soin d'entretien régulier", price: 49 },
  { id: '3', title: 'Option ponctuelle', details: 'Traitement anti-mousse longue durée', price: 45 },
  { id: '4', title: 'Option ponctuelle', details: 'Pierre ravivée', price: 50 },
  { id: '5', title: 'Option ponctuelle', details: 'Lettre ravivée', price: 50 },
];

export default function Pricing() {
  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="Nos tarifs"
        subtitle="Le respect de nos défunts ne doit pas être un coût"
      />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center justify-center gap-8">
          <div className="w-full overflow-hidden rounded-[var(--radius-lg)] lg:bg-[linear-gradient(180deg,#fff9ef_0%,#f7efdf_45%,#efe3cb_100%)] lg:shadow-2xl">
            <div className="hidden md:block">
              <table className="w-full border-collapse">
                <thead>
                  <tr className="border-b border-[#c7a96a]/40 bg-[linear-gradient(120deg,#f2e0ba_0%,#ead3a2_45%,#ddbf83_100%)]">
                    <th className="px-6 py-5 text-left text-sm font-semibold text-[var(--foreground)]">Formules</th>
                    <th className="px-6 py-5 text-left text-sm font-semibold text-[var(--foreground)]">Précisions</th>
                    <th className="px-6 py-5 text-left text-sm font-semibold text-[var(--foreground)]">Prix</th>
                  </tr>
                </thead>

                <tbody>
                  {prices.map((price, index) => (
                    <tr key={price.id} className={index !== prices.length - 1 ? 'border-b border-[#c7a96a]/30' : ''}>
                      <td className="px-6 py-5 font-semibold text-[var(--foreground)]">{price.title}</td>
                      <td className="px-6 py-5 text-[var(--muted-foreground)] italic !font-light">{price.details}</td>
                      <td className="px-6 py-5 text-[var(--foreground)] flex gap-2 items-center justify-start">
                        {price.price}
                        {typeof price.price === 'number' && <Euro className="w-4 h-4" />}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div className="flex flex-col gap-6 p-4 md:hidden">
              {prices.map((price) => (
                <CardComponent
                  key={price.id}
                  className="!min-h-lg !items-center !justify-center !gap-2 !p-4 !text-center"
                >
                  <div className="flex flex-col gap-3 justify-center items-center">
                    <h3 className="font-medium text-[var(--foreground)] text-2xl">{price.title}</h3>
                    <h4 className="text-[var(--foreground)] italic !font-light !text-xs">{price.details}</h4>

                    <p className="text-[var(--foreground)] flex gap-2 items-center justify-start">
                      {price.price} {typeof price.price === 'number' && <Euro className="w-4 h-4" />}
                    </p>
                  </div>
                </CardComponent>
              ))}
            </div>
          </div>
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

            <CardComponent
              icon={Handshake}
              title="Votre satisfaction garantie"
              description="Si le résultat ne vous convient pas, nous revenons gratuitement pour parfaire le travail. Votre satisfaction est notre priorité."
              className="flex-1 basis-[450px]"
            />
          </div>
        </div>
      </section>
    </>
  );
}
