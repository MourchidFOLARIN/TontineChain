import { Composition } from 'remotion';
import { TontinePresentation } from './Composition';
import './style.css';

export const RemotionRoot: React.FC = () => {
  return (
    <>
      <Composition
        id="TontinePresentation"
        component={TontinePresentation}
        durationInFrames={1980}
        fps={30}
        width={1920}
        height={1080}
      />
    </>
  );
};
