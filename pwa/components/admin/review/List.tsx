import { FieldGuesser, ListGuesser, type ListGuesserProps } from "@api-platform/admin";
import { useRecordContext, type UseRecordContextParams } from "react-admin";
import Rating from "@mui/material/Rating";

const RatingField = (props: UseRecordContextParams) => {
    const record = useRecordContext(props);

    return record ? <Rating value={record.rating} readOnly /> : null;
};
RatingField.defaultProps = { label: "Rating" };

export const List = (props: ListGuesserProps) => (
    <ListGuesser {...props} title="Reviews">
        <FieldGuesser source="user"/>
        <FieldGuesser source="book"/>
        <FieldGuesser source="publishedAt"/>
        <RatingField source="rating"/>
        <FieldGuesser source="body"/>
    </ListGuesser>
);
