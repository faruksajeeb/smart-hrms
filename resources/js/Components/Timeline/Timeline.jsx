import TimelineItem
from "./TimelineItem";

export default function Timeline({

    items = [],
    getTitle

}) {

    return (

        <div className="space-y-6  p-6 ">

            {items.map((item, index) => (

                <TimelineItem
                    key={item.id}
                    item={item}
                    isLast={index === items.length - 1}
                    getTitle={getTitle}
                />

            ))}

        </div>

    );

}