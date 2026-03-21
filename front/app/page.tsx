import { ButtonComponent, SectionDivider } from '#ui';
import MapComponent from '#/components/ui/Map.dynamic';
import { Clock, HeartHandshake, ShieldCheck } from 'lucide-react';
import HeroPicture from '../public/assets/hero_picture.webp';

export default function Home() {
  return (
    <>
      <section
        className="min-h-[75svh] -mt-[var(--nav-height)] mb-10 bg-no-repeat bg-cover bg-center shadow-2xl rounded-bl-[var(--radius-lg)] rounded-br-[var(--radius-lg)]"
        style={{ backgroundImage: `url(${HeroPicture.src})` }}
      >
        <div className="relative w-full min-h-[75svh] bg-[var(--bg-overlay)] rounded-bl-[var(--radius-lg)] rounded-br-[var(--radius-lg)] flex flex-col gap-5 justify-center items-center text-[var(--color-white)]">
          <h1 className="text-center max-w-[70%] text-3xl lg:text-4xl">
            Nous entretenons les sépultures de vos proches pour que chaque souvenir perdure.
          </h1>

          <h2 className="text-center max-w-[75%] text-2xl lg:text-2xl">
            Service professionnel de nettoyage et d&apos;entretien de sépultures à Caen et alentours
          </h2>

          <div className="absolute w-1/2 bottom-0 translate-y-1/2 flex flex-wrap gap-2 lg:gap-4 justify-center items-center">
            <div className="flex-1 basis-[250px]">
              <ButtonComponent variant="gold" size="xlf">
                Contactez-nous
              </ButtonComponent>
            </div>
            <div className="hidden lg:flex flex-1 basis-[250px]">
              <ButtonComponent variant="goldSecondary" size="xlf">
                Découvrez nos services
              </ButtonComponent>
            </div>
          </div>
        </div>
      </section>

      <section className="pt-10 pb-15 px-10 lg:px-20  why-section">
        <div className="flex flex-col items-center gap-8">
          <h2 className="text-center max-w-[75%] text-3xl lg:text-4xl">Pourquoi choisir nos services ?</h2>

          <div className="w-full flex gap-5 flex-col lg:flex-row items-center">
            <div className="feature-card flex-1 basis-[250px] flex flex-col items-center justify-center gap-3">
              <Clock size={40} aria-hidden />
              <h4 className="!text-lg font-semibold">Ponctualité</h4>
              <div className="text-sm text-center italic">
                Interventions planifiées et réalisées dans les délais convenus.
              </div>
            </div>

            <div className="feature-card--highlight flex-[1.08] basis-[250px] flex flex-col items-center justify-center gap-3">
              <HeartHandshake size={40} aria-hidden />
              <h4 className="!text-lg font-semibold">Respect</h4>
              <span className="text-sm text-center italic">
                Une approche délicate et respectueuse de la mémoire de vos proches.
              </span>
            </div>

            <div className="feature-card flex-1 basis-[250px] flex flex-col items-center justify-center gap-3">
              <ShieldCheck size={40} aria-hidden />
              <h4 className="!text-lg font-semibold">Professionnalisme</h4>
              <div className="text-sm text-center italic">
                Un service de qualité avec des produits adaptés et respectueux.
              </div>
            </div>
          </div>
        </div>
      </section>

      <SectionDivider />

      <section className="pt-10 pb-15 px-10 lg:px-20 flex flex-wrap gap-8 items-center">
        <div className="flex-2 basis-[420px] flex flex-col gap-4">
          <h2 className="text-3xl lg:text-4xl">Un service local, organisé et fiable</h2>
          <p className="leading-6">
            Nous intervenons à Caen et ses environs afin de veiller à entretenir les sépultures pour que chaque lieu
            reste digne, soigné et apaisant au fil du temps.
            <br />
            Un service simple, pensé pour vous accompagner sans contrainte.
          </p>
        </div>

        <div className="flex-3 basis-[600px]">
          <MapComponent mode="circle" center={[49.1829, -0.3707]} radiusKm={15} zoomBoost={1} />
        </div>
      </section>
    </>
  );
}
