import type {
    JsComponentProps,
    JsComponentRenderer,
} from '@filament/schemas/js-component';
import { createRoot } from 'react-dom/client';
import EventCard from '@/components/ticketing/event-card';
import type { EventSummary } from '@/types/ticketing';

function EventCardPreview({ configuration }: JsComponentProps<EventSummary>) {
    return (
        <figure className="mx-auto w-full max-w-sm space-y-3">
            <figcaption className="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                How customers see this event
            </figcaption>
            <EventCard event={configuration} />
        </figure>
    );
}

const mountEventCardPreview: JsComponentRenderer<EventSummary> = ({
    host,
    props: initialProps,
}) => {
    const root = createRoot(host);
    const update = (props: JsComponentProps<EventSummary>) =>
        root.render(<EventCardPreview {...props} />);

    update(initialProps);

    return { update, destroy: () => root.unmount() };
};

export default mountEventCardPreview;
