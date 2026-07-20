import TimelineStatusBadge
from "./TimelineStatusBadge";

import TimelineConnector
from "./TimelineConnector";

export default function TimelineItem({

    item,
    isLast,
    getTitle

}) {

    return (

        <div className="flex">

            <div className="mr-6 flex flex-col items-center">

                <div
                    className="h-4 w-4 rounded-full bg-indigo-600"
                />

                {!isLast && (

                    <TimelineConnector />

                )}

            </div>

            <div className="flex-1 rounded-xl border bg-white p-5 shadow-sm">

                <div className="flex items-center justify-between">

                    <h4 className="font-semibold text-lg">

                        {getTitle(item)}

                    </h4>

                    <TimelineStatusBadge
                        item={item}
                    />

                </div>

                <div className="mt-5 grid grid-cols-4 gap-6">

                    <div>

                        <p className="text-xs text-gray-500">

                            Effective From

                        </p>

                        <p>

                            {item.effective_from}

                        </p>

                    </div>

                    <div>

                        <p className="text-xs text-gray-500">

                            Effective To

                        </p>

                        <p>

                            {item.effective_to ??
                                "Present"}

                        </p>

                    </div>

                    <div>

                        <p className="text-xs text-gray-500">

                            Assignment Type

                        </p>

                        <p>

                            {item.assignment_type}

                        </p>

                    </div>

                    <div>

                        <p className="text-xs text-gray-500">

                            Changed By

                        </p>

                        <p>

                            {item.creator?.name ??
                                "-"}

                        </p>

                    </div>

                </div>

                {item.remarks && (

                    <div className="mt-4 rounded-lg bg-slate-50 p-4">

                        {item.remarks}

                    </div>

                )}

            </div>

        </div>

    );

}