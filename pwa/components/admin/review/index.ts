import { ReviewsList } from "./ReviewsList";
import { ReviewsEdit } from "./ReviewsEdit";
import { ReviewsShow } from "./ReviewsShow";
import { type Review } from "../../../types/Review";

const reviewResourceProps = {
  list: ReviewsList,
  edit: ReviewsEdit,
  show: ReviewsShow,
  hasCreate: false,
  recordRepresentation: (record: Review) => record.user.name,
};

export default reviewResourceProps;
