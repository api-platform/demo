import React from "react";

const AddressField = ({ record }) =>
    record ? (
        <span>
            {record.address}, {record.city} {record.zipcode}
        </span>
    ) : null;

export default AddressField;
