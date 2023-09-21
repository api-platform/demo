import { useRecordContext, UseRecordContextParams } from "react-admin";
import Rating from "@mui/material/Rating";

export const RatingField = (props: UseRecordContextParams) => {
  const record = useRecordContext(props);

  return !!record && typeof record.rating === "number" ? <Rating value={record.rating} readOnly/> : null;
};
RatingField.defaultProps = { label: "Rating" };
