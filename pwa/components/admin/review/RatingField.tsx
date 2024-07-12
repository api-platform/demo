import { useRecordContext } from "react-admin";
import Rating from "@mui/material/Rating";

export const RatingField = () => {
  const record = useRecordContext();
  return !!record && typeof record.rating === "number" ? (
    <Rating value={record.rating} readOnly />
  ) : null;
};
