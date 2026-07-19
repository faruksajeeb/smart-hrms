import TimelineItem
from "./TimelineItem";

export default function Timeline({

    items = [],

}) {

    return (

        <div className="space-y-6  p-6 ">

            {items.map((item, index) => (

                <TimelineItem

                    key={item.id}

                    item={item}

                    isLast={
                        index ===
                        items.length - 1
                    }

                />

            ))}

        </div>

    );

}