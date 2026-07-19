export default function TimelineStatusBadge({ item }) {

    const today = new Date();

    const from = new Date(item.effective_from);

    const to = item.effective_to
        ? new Date(item.effective_to)
        : null;

    let label = "Historical";
    let classes =
        "bg-gray-100 text-gray-700";

    if (!to) {

        label = "Current";

        classes =
            "bg-green-100 text-green-700";

    }
    else if (from > today) {

        label = "Future";

        classes =
            "bg-blue-100 text-blue-700";

    }

    return (

        <span
            className={`rounded-full px-3 py-1 text-xs font-semibold ${classes}`}
        >
            {label}
        </span>

    );

}