import { FieldGuesser, type ListGuesserProps } from "@api-platform/admin";
import {
    TextInput,
    Pagination,
    Datagrid,
    type PaginationProps,
    useRecordContext,
    type UseRecordContextParams,
    SelectInput,
    Button,
    List as ReactAdminList,
    EditButton, ShowButtonProps
} from "react-admin";
import VisibilityIcon from "@mui/icons-material/Visibility";
import slugify from "slugify";
import {getItemPath} from "@/utils/dataAccess";

const ConditionField = (props: UseRecordContextParams) => {
    const record = useRecordContext(props);

    // todo translate condition
    return record ? <span>{record.condition.replace("https://schema.org/", "")}</span> : null;
};
ConditionField.defaultProps = { label: "Condition" };

const filters = [
    <TextInput label="Title" source="title"/>,
    <TextInput label="Author" source="author"/>,
    <SelectInput label="Condition" source="condition" choices={[
        /*todo translate condition*/
        { id: "https://schema.org/NewCondition", name: "NewCondition" },
        { id: "https://schema.org/RefurbishedCondition", name: "RefurbishedCondition" },
        { id: "https://schema.org/DamagedCondition", name: "DamagedCondition" },
        { id: "https://schema.org/UsedCondition", name: "UsedCondition" },
    ]}/>,
];

const PostPagination = (props: PaginationProps) => <Pagination rowsPerPageOptions={[]} {...props} />;

const ShowButton = (props: ShowButtonProps) => {
    const record = useRecordContext(props);

    return record ? (
        // @ts-ignore
        <Button label="Show" target="_blank" href={getItemPath({
            id: record.id.replace(/^\/admin\/books\//, ""),
            slug: slugify(`${record.title}-${record.author}`, { lower: true, trim: true, remove: /[*+~.(),;'"!:@]/g }),
        }, "/books/[id]/[slug]")}>
            <VisibilityIcon/>
        </Button>
    ) : null;
};

export const List = (props: ListGuesserProps) => (
    <ReactAdminList {...props} filters={filters} pagination={<PostPagination/>} exporter={false} title="Books">
        <Datagrid>
            <FieldGuesser source="title"/>
            <FieldGuesser source="author"/>
            <ConditionField source="condition"/>
            <ShowButton/>
            <EditButton/>
        </Datagrid>
    </ReactAdminList>
);
