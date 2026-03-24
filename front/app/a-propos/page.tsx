import { HeroComponent } from '#ui';
import HeroPicture from '../../public/assets/about_hero_picture.webp';

export default function About() {
  return (
    <>
      <HeroComponent
        picture={HeroPicture}
        title="À propos de nous"
        subtitle="Ne confiez pas les sépultures de vos défunts à n'importe qui."
      />

      <section className="page-shell page-section">
        <div className="flex flex-col items-center justify-center gap-10 lg:gap-8">
          <div className="w-full flex flex-col lg:flex-row gap-5 lg:gap-10">
            <div
              className="services-image-frame !p-0 bg-no-repeat bg-cover bg-center flex-1 hidden lg:block"
              style={{ backgroundImage: `url(${HeroPicture.src})` }}
            />

            <div className="flex-1 flex flex-col justify-center gap-3 pb-4 lg:py-4">
              <h3 className="px-5 text-3xl lg:text-4xl">La lettre d&apos;Émilie</h3>
              <p className="italic leading-6 indent-8 px-5">
                Il faudrait que tu me donnes ton texte ici et que tu me donnes une belle photo de toi (photo claire et pro mais pas trop je pense).
              </p>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
